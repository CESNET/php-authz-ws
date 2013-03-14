<?php

namespace InAuthzWs\Client;


class ClientFactory implements ClientFactoryInterface
{


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Client\ClientFactoryInterface::createClient()
     */
    public function createClient(array $clientInfo)
    {
        if (! isset($clientInfo['id'])) {
            throw new Exception\ClientCreateException('No client ID');
        }
        
        $client = new Client($clientInfo['id']);
        
        if (isset($clientInfo['description'])) {
            $client->setDescription($clientInfo['description']);
        }
        
        if (isset($clientInfo['authentication'])) {
            $authData = $clientInfo['authentication'];
            $type = null;
            if (! isset($authData['type'])) {
                throw new Exception\ClientCreateException('Authentication info with no type information');
            }
            
            $type = $authData['type'];
            
            $options = array();
            if (isset($authData['options'])) {
                $options = $authData['options'];
            }
            
            $authenticationInfo = new AuthenticationInfo($type, $options);
            $client->setAuthenticationInfo($authenticationInfo);
        }
        
        return $client;
    }
}