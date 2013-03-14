<?php

namespace InAuthzWsTest\Client;

use InAuthzWs\Client\ClientFactory;


class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ClientFactory
     */
    protected $factory = null;


    public function setUp()
    {
        $this->factory = new ClientFactory();
    }


    public function testCreateClientWithNoClientId()
    {
        $this->setExpectedException('InAuthzWs\Client\Exception\ClientCreateException');
        $this->factory->createClient(array());
    }


    public function testCreateClientWithNoAuth()
    {
        $info = array(
            'id' => 'testclient', 
            'description' => 'testdescription'
        );
        
        $client = $this->factory->createClient($info);
        
        $this->assertSame('testclient', $client->getId());
        $this->assertSame('testdescription', $client->getDescription());
        $this->assertNull($client->getAuthenticationInfo());
    }


    public function testCreateClientWithInvalidAuth()
    {
        $this->setExpectedException('InAuthzWs\Client\Exception\ClientCreateException');
        
        $info = array(
            'id' => 'testclient', 
            'description' => 'testdescription', 
            'authentication' => array(
                'foo' => 'bar'
            )
        );
        
        $client = $this->factory->createClient($info);
    }


    public function testCreateClient()
    {
        $authInfo = array(
            'type' => 'testtype', 
            'options' => array(
                'foo' => 'bar'
            )
        );
        $info = array(
            'id' => 'testclient', 
            'description' => 'testdescription', 
            'authentication' => $authInfo
        );
        
        $client = $this->factory->createClient($info);
        $this->assertSame('testclient', $client->getId());
        $this->assertSame('testdescription', $client->getDescription());
        
        $authenicationInfo = $client->getAuthenticationInfo();
        $this->assertSame($authInfo['type'], $authenicationInfo->getType());
        $this->assertSame($authInfo['options'], $authenicationInfo->getOptions());
    }
}