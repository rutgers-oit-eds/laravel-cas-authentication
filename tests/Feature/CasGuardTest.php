<?php

namespace Rutgers\Cas\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Rutgers\Cas\CasGuard;
use Rutgers\Cas\CasManager;
use Rutgers\Cas\Exceptions\CasAuthorizationException;
use Rutgers\Cas\Facades\Cas;
use Rutgers\Cas\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CasGuard::class)]
class CasGuardTest extends TestCase
{
    private Session $session;
    private UserProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->session  = $this->createMock(Session::class);
        $this->provider = $this->createMock(UserProvider::class);
    }

    protected function tearDown(): void
    {
        Cas::clearResolvedInstances();
        parent::tearDown();
    }

    private function makeGuard(?Request $request = null): CasGuard
    {
        return new CasGuard('web', $this->provider, $this->session, $request);
    }

    /** Set up Cas facade to return a given authentication state. */
    private function swapCas(bool $authenticated): void
    {
        $mock = $this->createMock(CasManager::class);
        $mock->method('isAuthenticated')->willReturn($authenticated);
        Cas::swap($mock);
    }

    private function makeUser(string $id = 'jdoe'): Authenticatable
    {
        $user = $this->createMock(Authenticatable::class);
        $user->method('getAuthIdentifier')->willReturn($id);
        return $user;
    }

    private function sessionKey(): string
    {
        return 'login_web_' . sha1(CasGuard::class);
    }

    // -------------------------------------------------------------------------
    // user()
    // -------------------------------------------------------------------------

    public function test_user_returns_null_when_cas_not_authenticated(): void
    {
        $this->swapCas(false);

        $this->assertNull($this->makeGuard()->user());
    }

    public function test_user_returns_null_when_logged_out_flag_is_set(): void
    {
        $this->swapCas(true);
        $guard = $this->makeGuard();

        $prop = new \ReflectionProperty(CasGuard::class, 'loggedOut');
        $prop->setAccessible(true);
        $prop->setValue($guard, true);

        $this->assertNull($guard->user());
    }

    public function test_user_returns_cached_value_without_querying_session(): void
    {
        $this->swapCas(true);
        $user = $this->makeUser();

        $this->session->expects($this->never())->method('get');

        $guard = $this->makeGuard();
        $guard->setUser($user);

        $this->assertSame($user, $guard->user());
    }

    public function test_user_retrieves_from_session_via_provider(): void
    {
        $this->swapCas(true);
        $user = $this->makeUser();

        $this->session->method('get')->with($this->sessionKey())->willReturn('jdoe');
        $this->provider->method('retrieveById')->with('jdoe')->willReturn($user);

        $this->assertSame($user, $this->makeGuard()->user());
    }

    public function test_user_returns_null_when_session_id_not_found_by_provider(): void
    {
        $this->swapCas(true);

        $this->session->method('get')->with($this->sessionKey())->willReturn('unknown');
        $this->provider->method('retrieveById')->willReturn(null);

        $this->assertNull($this->makeGuard()->user());
    }

    // -------------------------------------------------------------------------
    // id()
    // -------------------------------------------------------------------------

    public function test_id_returns_null_when_logged_out(): void
    {
        $guard = $this->makeGuard();

        $prop = new \ReflectionProperty(CasGuard::class, 'loggedOut');
        $prop->setAccessible(true);
        $prop->setValue($guard, true);

        $this->assertNull($guard->id());
    }

    public function test_id_returns_auth_identifier_when_user_is_authenticated(): void
    {
        $this->swapCas(true);
        $user = $this->makeUser('jdoe');

        $this->session->method('get')->with($this->sessionKey())->willReturn('jdoe');
        $this->provider->method('retrieveById')->with('jdoe')->willReturn($user);

        $this->assertSame('jdoe', $this->makeGuard()->id());
    }

    public function test_id_falls_back_to_session_when_user_returns_null(): void
    {
        $this->swapCas(false); // user() returns null

        $this->session->method('get')->with($this->sessionKey())->willReturn('jdoe');

        $this->assertSame('jdoe', $this->makeGuard()->id());
    }

    // -------------------------------------------------------------------------
    // login()
    // -------------------------------------------------------------------------

    public function test_login_stores_session_and_sets_user(): void
    {
        $this->swapCas(true);
        $user = $this->makeUser('jdoe');

        $this->provider->method('retrieveById')->with('jdoe')->willReturn($user);
        $this->session->expects($this->once())->method('put')->with($this->sessionKey(), 'jdoe');
        $this->session->expects($this->once())->method('migrate')->with(true);

        $guard = $this->makeGuard();
        $guard->login('jdoe');

        $this->assertTrue($guard->hasUser());
        $this->assertSame($user, $guard->user());
    }

    public function test_login_throws_when_user_not_found_in_provider(): void
    {
        $this->provider->method('retrieveById')->willReturn(null);

        $this->expectException(CasAuthorizationException::class);

        $this->makeGuard()->login('unknown_user');
    }

    public function test_login_exception_carries_the_net_id(): void
    {
        $this->provider->method('retrieveById')->willReturn(null);

        try {
            $this->makeGuard()->login('baduser');
            $this->fail('Expected CasAuthorizationException');
        } catch (CasAuthorizationException $e) {
            $this->assertSame('baduser', $e->netID());
        }
    }

    // -------------------------------------------------------------------------
    // logout()
    // -------------------------------------------------------------------------

    public function test_logout_removes_session_key_and_nulls_user(): void
    {
        $reqSession = $this->createMock(Session::class);
        $reqSession->expects($this->once())->method('flush');

        $request = new class($reqSession) extends Request {
            public function __construct(private readonly Session $sess)
            {
                parent::__construct();
            }

            public function session(): Session
            {
                return $this->sess;
            }
        };

        $this->session->expects($this->once())->method('remove')->with($this->sessionKey());

        $guard = $this->makeGuard($request);
        $guard->logout();

        $this->assertFalse($guard->hasUser());
    }

    // -------------------------------------------------------------------------
    // updateSession()
    // -------------------------------------------------------------------------

    public function test_update_session_puts_id_and_migrates(): void
    {
        $this->session->expects($this->once())->method('put')->with($this->sessionKey(), 'jdoe');
        $this->session->expects($this->once())->method('migrate')->with(true);

        $this->makeGuard()->updateSession('jdoe');
    }

    // -------------------------------------------------------------------------
    // getName()
    // -------------------------------------------------------------------------

    public function test_get_name_follows_expected_format(): void
    {
        $expected = 'login_web_' . sha1(CasGuard::class);

        $this->assertSame($expected, $this->makeGuard()->getName());
    }

    // -------------------------------------------------------------------------
    // viaRemember()
    // -------------------------------------------------------------------------

    public function test_via_remember_is_false_by_default(): void
    {
        $this->assertFalse($this->makeGuard()->viaRemember());
    }

    // -------------------------------------------------------------------------
    // onceUsingId()
    // -------------------------------------------------------------------------

    public function test_once_using_id_returns_user_when_found(): void
    {
        $user = $this->makeUser('jdoe');
        $this->provider->method('retrieveById')->with('jdoe')->willReturn($user);

        $result = $this->makeGuard()->onceUsingId('jdoe');

        $this->assertSame($user, $result);
    }

    public function test_once_using_id_returns_false_when_not_found(): void
    {
        $this->provider->method('retrieveById')->willReturn(null);

        $this->assertFalse($this->makeGuard()->onceUsingId('unknown'));
    }

    // -------------------------------------------------------------------------
    // dispatcher & request accessors
    // -------------------------------------------------------------------------

    public function test_set_and_get_dispatcher(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $guard = $this->makeGuard();

        $guard->setDispatcher($dispatcher);

        $this->assertSame($dispatcher, $guard->getDispatcher());
    }

    public function test_set_request_returns_guard_and_updates_request(): void
    {
        $request = Request::createFromGlobals();
        $guard   = $this->makeGuard();

        $result = $guard->setRequest($request);

        $this->assertSame($guard, $result);
        $this->assertSame($request, $guard->getRequest());
    }

    public function test_get_request_returns_globals_when_no_request_injected(): void
    {
        $this->assertInstanceOf(Request::class, $this->makeGuard()->getRequest());
    }
}
