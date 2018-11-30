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

    public function test_login_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('login'));
    }

    public function test_logout_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('logout'));
    }
}
