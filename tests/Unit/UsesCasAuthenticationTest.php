<?php

namespace Rutgers\Cas\Tests\Unit;

use Rutgers\Cas\Traits\UsesCasAuthentication;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsesCasAuthentication::class)]
class UsesCasAuthenticationTest extends TestCase
{
    public function test_auth_identifier_name_is_cas_username(): void
    {
        $model = new class {
            use UsesCasAuthentication;
        };

        $this->assertSame('cas_username', $model->getAuthIdentifierName());
    }
}
