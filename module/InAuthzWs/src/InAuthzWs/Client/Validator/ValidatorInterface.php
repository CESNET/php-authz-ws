<?php

namespace InAuthzWs\Client\Validator;

use InAuthzWs\Client\Client;


interface ValidatorInterface
{


    public function validate(Client $client);
}