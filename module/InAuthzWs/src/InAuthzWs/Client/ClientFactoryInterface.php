<?php

namespace InAuthzWs\Client;


interface ClientFactoryInterface
{


    /**
     * Creates a client instance.
     * 
     * @param array $clientInfo
     * @return Client
     */
    public function createClient(array $clientInfo);
}