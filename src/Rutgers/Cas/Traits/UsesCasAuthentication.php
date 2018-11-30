<?php

namespace Rutgers\Cas\Traits;

trait UsesCasAuthentication
{

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'cas_username';
    }

}