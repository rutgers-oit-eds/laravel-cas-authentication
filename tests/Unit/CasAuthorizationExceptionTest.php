<?php

namespace Rutgers\Cas\Tests\Unit;

use Rutgers\Cas\Exceptions\CasAuthorizationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CasAuthorizationException::class)]
class CasAuthorizationExceptionTest extends TestCase
{
    public function test_net_id_is_returned(): void
    {
        $exception = new CasAuthorizationException('jdoe');

        $this->assertSame('jdoe', $exception->netID());
    }

    public function test_is_an_exception(): void
    {
        $this->assertInstanceOf(\Exception::class, new CasAuthorizationException('jdoe'));
    }

    public function test_net_id_preserves_arbitrary_string(): void
    {
        $exception = new CasAuthorizationException('user_with-special.chars@123');

        $this->assertSame('user_with-special.chars@123', $exception->netID());
    }
}
