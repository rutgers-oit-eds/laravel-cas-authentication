<?php

namespace Rutgers\Cas\Tests\Unit;

use Rutgers\Cas\CasManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Subclass that skips phpCAS static initialization so config/masquerade
 * logic can be tested without a running CAS server.
 */
class TestableCasManager extends CasManager
{
    public function __construct(array $config)
    {
        $this->parseConfig($config);
        if (!empty($this->config['cas_masquerade'])) {
            $this->_masquerading = true;
        }
    }

    public function setAttributes(array $attr): void
    {
        $this->_attributes = $attr;
    }
}

#[CoversClass(CasManager::class)]
class CasManagerTest extends TestCase
{
    private function make(array $overrides = []): TestableCasManager
    {
        return new TestableCasManager(
            array_merge(['cas_hostname' => 'cas.example.com'], $overrides)
        );
    }

    // --- config defaults ---

    public function test_default_port_is_443(): void
    {
        $this->assertSame(443, $this->make()->getConfig()['cas_port']);
    }

    public function test_default_uri_is_slash_cas(): void
    {
        $this->assertSame('/cas', $this->make()->getConfig()['cas_uri']);
    }

    public function test_default_session_name_is_casauth(): void
    {
        $this->assertSame('CASAuth', $this->make()->getConfig()['cas_session_name']);
    }

    public function test_default_session_lifetime_is_7200(): void
    {
        $this->assertSame(7200, $this->make()->getConfig()['cas_session_lifetime']);
    }

    public function test_default_version_is_2_0(): void
    {
        $this->assertSame('2.0', $this->make()->getConfig()['cas_version']);
    }

    public function test_default_boolean_flags(): void
    {
        $config = $this->make()->getConfig();

        $this->assertFalse($config['cas_proxy']);
        $this->assertFalse($config['cas_debug']);
        $this->assertFalse($config['cas_verbose_errors']);
        $this->assertFalse($config['cas_control_session']);
        $this->assertTrue($config['cas_enable_saml']);
        $this->assertTrue($config['cas_validate_cn']);
        $this->assertTrue($config['cas_session_httponly']);
    }

    public function test_provided_values_override_defaults(): void
    {
        $config = $this->make([
            'cas_port'    => 8443,
            'cas_uri'     => '/sso',
            'cas_version' => '3.0',
            'cas_proxy'   => true,
        ])->getConfig();

        $this->assertSame(8443, $config['cas_port']);
        $this->assertSame('/sso', $config['cas_uri']);
        $this->assertSame('3.0', $config['cas_version']);
        $this->assertTrue($config['cas_proxy']);
    }

    public function test_hostname_is_stored_in_config(): void
    {
        $this->assertSame('cas.university.edu', $this->make(['cas_hostname' => 'cas.university.edu'])->getConfig()['cas_hostname']);
    }

    // --- masquerade ---

    public function test_is_not_masquerading_by_default(): void
    {
        $this->assertFalse($this->make()->isMasquerading());
    }

    public function test_is_masquerading_when_cas_masquerade_is_set(): void
    {
        $this->assertTrue($this->make(['cas_masquerade' => 'testuser'])->isMasquerading());
    }

    public function test_user_returns_masquerade_name(): void
    {
        $this->assertSame('testuser', $this->make(['cas_masquerade' => 'testuser'])->user());
    }

    public function test_get_current_user_matches_user(): void
    {
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $this->assertSame($manager->user(), $manager->getCurrentUser());
    }

    public function test_is_authenticated_when_masquerading(): void
    {
        $this->assertTrue($this->make(['cas_masquerade' => 'testuser'])->isAuthenticated());
    }

    public function test_check_authentication_when_masquerading(): void
    {
        $this->assertTrue($this->make(['cas_masquerade' => 'testuser'])->checkAuthentication());
    }

    // --- attributes (only meaningful when masquerading) ---

    public function test_set_and_get_attribute_when_masquerading(): void
    {
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $manager->setAttributes(['email' => 'user@example.com']);

        $this->assertSame('user@example.com', $manager->getAttribute('email'));
    }

    public function test_get_attribute_returns_null_for_missing_key(): void
    {
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $manager->setAttributes([]);

        $this->assertNull($manager->getAttribute('missing'));
    }

    public function test_has_attribute_true_when_present(): void
    {
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $manager->setAttributes(['role' => 'admin']);

        $this->assertTrue($manager->hasAttribute('role'));
    }

    public function test_has_attribute_false_when_absent(): void
    {
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $manager->setAttributes([]);

        $this->assertFalse($manager->hasAttribute('role'));
    }

    public function test_get_attributes_returns_all_when_masquerading(): void
    {
        $attrs = ['email' => 'a@b.com', 'role' => 'admin'];
        $manager = $this->make(['cas_masquerade' => 'testuser']);
        $manager->setAttributes($attrs);

        $this->assertSame($attrs, $manager->getAttributes());
    }

    // --- __call passthrough ---

    public function test_call_throws_bad_method_call_for_unknown_method(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->make()->nonExistentCasMethod();
    }
}
