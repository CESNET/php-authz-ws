<?php

namespace InAuthzWs\Client\Validator;

use InAuthzWs\Client\Client;


class Simple implements ValidatorInterface
{

    protected $authenticationType = 'secret';


    public function setAuthenticationType($authenticationType)
    {
        $this->authenticationType = $authenticationType;
    }


    public function validate(Client $client)
    {
        if (! $client->getId()) {
            throw new Exception\InvalidClientException('Empty client ID');
        }
        
        $authenticationInfo = $client->getAuthenticationInfo();
        if (null === $authenticationInfo) {
            throw new Exception\InvalidClientException(sprintf("Client '%s' has no authentication info registered", $client->getId()));
        }
        
        if ($this->authenticationType !== $authenticationInfo->getType()) {
            throw new Exception\InvalidClientException(sprintf("Wrong authentication type '%s' for client '%s'", $authenticationInfo->getType(), $client->getId()));
        }
        
        $options = $authenticationInfo->getOptions();
        if (! isset($options['secret'])) {
            throw new Exception\InvalidClientException(sprintf("Missing authentication configuration for client '%s'", $client->getId()));
        }
    }
}