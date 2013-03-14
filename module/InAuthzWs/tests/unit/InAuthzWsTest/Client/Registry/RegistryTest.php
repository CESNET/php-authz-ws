<?php

namespace InAuthzWsTest\Client\Registry;

use InAuthzWs\Client\Registry\Registry;
use InAuthzWs;


class RegistryTest extends \PHPUnit_Framework_TestCase
{


    public function testGetClientData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $registry = $this->createRegistry($data);
        $this->assertSame($data, $registry->getClientData());
    }


    public function testGetClientByIdNotFound()
    {
        $data = array(
            array(
                'id' => 'clientId'
            )
        );
        
        $registry = $this->createRegistry($data);
        $this->assertNull($registry->getClientById('otherId'));
    }


    public function testGetClientById()
    {
        $clientInfo = array(
            'id' => 'clientId'
        );
        $data = array(
            $clientInfo
        );
        
        $registry = $this->createRegistry($data);
        
        $client = $this->getMockBuilder('InAuthzWs\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        
        $clientFactory = $this->getMock('InAuthzWs\Client\ClientFactoryInterface');
        $clientFactory->expects($this->once())
            ->method('createClient')
            ->with($clientInfo)
            ->will($this->returnValue($client));
        $registry->setClientFactory($clientFactory);
        
        $returnedClient = $registry->getClientById('clientId');
        
        $this->assertSame($client, $returnedClient);
    }


    protected function createRegistry(array $clientData)
    {
        $storage = $this->getMock('InAuthzWs\Client\Registry\Storage\StorageInterface');
        $storage->expects($this->once())
            ->method('loadData')
            ->will($this->returnValue($clientData));
        
        $registry = new Registry($storage);
        
        return $registry;
    }
}