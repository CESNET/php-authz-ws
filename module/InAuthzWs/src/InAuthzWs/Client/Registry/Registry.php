<?php

namespace InAuthzWs\Client\Registry;

use InAuthzWs\Client\Registry\Storage\StorageInterface;
use Zend\Cache\Pattern\ClassCache;
use InAuthzWs\Client\Client;
use InAuthzWs\Client\ClientFactory;
use InAuthzWs\Client\ClientFactoryInterface;


/**
 * Client registry - used for manipulating client data.
 */
class Registry
{

    /**
     * Registry storage.
     * 
     * @var StorageInterface
     */
    protected $storage = null;

    /**
     * Client factory.
     * 
     * @var ClientFactoryInterface
     */
    protected $clientFactory = null;

    /**
     * Client data.
     * 
     * @var array
     */
    protected $clientData = null;


    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param ClientFactoryInterface $clientFactory
     */
    public function __construct(StorageInterface $storage, ClientFactoryInterface $clientFactory = null)
    {
        $this->storage = $storage;
        if (null !== $clientFactory) {}
    }


    /**
     * Sets the client factory.
     * 
     * @param ClientFactoryInterface $clientFactory
     */
    public function setClientFactory(ClientFactoryInterface $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }


    /**
     * Returns the client factory.
     * 
     * @return ClientFactoryInterface
     */
    public function getClientFactory()
    {
        if (! ($this->clientFactory instanceof ClientFactoryInterface)) {
            $this->clientFactory = new ClientFactory();
        }
        
        return $this->clientFactory;
    }


    /**
     * Retrieves the client by ID and returns a client instance.
     * 
     * @param string $id
     * @return Client|null
     */
    public function getClientById($id)
    {
        $client = null;
        
        if ($id) {
            $clientData = $this->getClientData();
            foreach ($clientData as $clientInfo) {
                if ($id === $clientInfo['id']) {
                    $client = $this->getClientFactory()
                        ->createClient($clientInfo);
                }
            }
        }
        
        return $client;
    }


    /**
     * Returns all client data.
     * @return array
     */
    public function getClientData()
    {
        if (null === $this->clientData) {
            $this->clientData = $this->storage->loadData();
        }
        
        return $this->clientData;
    }
}