<?php

namespace Rutgers\Cas\Tests\Feature;

use Rutgers\Cas\CasGuard;
use Rutgers\Cas\CasServiceProvider;
use Rutgers\Cas\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CasServiceProvider::class)]
class CasServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CasServiceProvider::class];
    }

    public function test_cas_singleton_is_bound_in_container(): void
    {
        $this->assertTrue($this->app->bound('cas'));
    }

    public function test_package_config_is_merged_when_not_published(): void
    {
        $this->assertSame('CASAuth', config('cas.cas_session_name'));
        $this->assertArrayHasKey('cas_session_domain', config('cas'));
        $this->assertArrayHasKey('cas_session_secure', config('cas'));
    }

    public function test_cas_guard_uses_configured_guard_name(): void
    {
        config([
            'auth.guards.web' => ['driver' => 'cas', 'provider' => 'users'],
        ]);

        $guard = $this->app['auth']->guard('web');

        $this->assertInstanceOf(CasGuard::class, $guard);
        $this->assertSame('login_web_' . sha1(CasGuard::class), $guard->getName());
    }

    public function test_login_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('login'));
    }

    public function test_logout_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('logout'));
    }
}
