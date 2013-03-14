<?php

namespace InAuthzWsTest\Client;

use InAuthzWs\Client\Client;


class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Client
     */
    protected $client = null;


    public function setUp()
    {
        $this->client = new Client('clientId');
    }


    public function testSetId()
    {
        $id = 'testId';
        $this->client->setId($id);
        $this->assertSame($id, $this->client->getId());
    }


    public function testSetDescription()
    {
        $desc = 'some desc';
        $this->client->setDescription($desc);
        $this->assertSame($desc, $this->client->getDescription());
    }


    public function testSetAuthenticationInfo()
    {
        $info = $this->getMockBuilder('InAuthzWs\Client\AuthenticationInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client->setAuthenticationInfo($info);
        $this->assertSame($info, $this->client->getAuthenticationInfo());
    }
}