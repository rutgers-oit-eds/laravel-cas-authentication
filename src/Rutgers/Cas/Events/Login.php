<?php

namespace Rutgers\Cas\Events;

use Illuminate\Queue\SerializesModels;

class Login
{
    use SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}