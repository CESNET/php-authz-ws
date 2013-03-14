<?php

namespace InAuthzWs\Client\Authenticator;

use Zend\Http\Request;


interface AuthenticatorInterface
{


    /**
     * Tries to authenticate the client by the HTTP request.
     * 
     * @param Request $request
     * @return Result
     */
    public function authenticate(Request $request);
}