<?php

namespace Rutgers\Cas\Tests\Feature;

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

    public function test_login_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('login'));
    }

    public function test_logout_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('logout'));
    }
}
