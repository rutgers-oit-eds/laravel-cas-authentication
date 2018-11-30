<?php

namespace Rutgers\Cas\Tests\Unit;

use Rutgers\Cas\Events\Login;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Login::class)]
class LoginEventTest extends TestCase
{
    public function test_user_is_stored_on_event(): void
    {
        $user = new \stdClass();
        $user->name = 'Test User';

        $event = new Login($user);

        $this->assertSame($user, $event->user);
    }
}
