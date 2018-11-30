<?php

namespace Rutgers\Cas\Exceptions;

class CasAuthorizationException extends \Exception
{
    protected $netID;

    public function __construct($netID)
    {
        $this->netID = $netID;
        parent::__construct();
    }

    public function netID()
    {
        return $this->netID;
    }
}
