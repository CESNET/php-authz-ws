<?php

namespace InAuthzWsTest\Client\Validator;

use InAuthzWs\Client\Validator\Simple;


class SimpleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Simple
     */
    protected $validator = null;


    public function setUp()
    {
        $this->validator = new Simple();
    }


    public function testValidateNoId()
    {
        $this->setExpectedException('InAuthzWs\Client\Validator\Exception\InvalidClientException');
        
        $client = $this->createClient(null, null);
        $this->validator->validate($client);
    }


    public function testValidateNoAuthInfo()
    {
        $this->setExpectedException('InAuthzWs\Client\Validator\Exception\InvalidClientException');
        
        $client = $this->createClient('testclient', null);
        $this->validator->validate($client);
    }


    public function testValidateWrongAuthType()
    {
        $this->setExpectedException('InAuthzWs\Client\Validator\Exception\InvalidClientException');
        
        $type = 'testtype';
        $info = $this->createAuthenticationInfo('anothertype');
        
        $this->validator->setAuthenticationType($type);
        $client = $this->createClient('testclient', $info);
        $this->validator->validate($client);
    }


    public function testValidateMissingOptions()
    {
        $this->setExpectedException('InAuthzWs\Client\Validator\Exception\InvalidClientException');
        
        $type = 'testtype';
        $options = array(
            'foo' => 'bar'
        );
        $info = $this->createAuthenticationInfo($type, $options);
        
        $this->validator->setAuthenticationType($type);
        $client = $this->createClient('testclient', $info);
        $this->validator->validate($client);
    }


    public function testValidateOk()
    {
        $type = 'testtype';
        $options = array(
            'secret' => 'bar'
        );
        $info = $this->createAuthenticationInfo($type, $options);
        
        $this->validator->setAuthenticationType($type);
        $client = $this->createClient('testclient', $info);
        $this->validator->validate($client);
    }
    
    //-----------------------------------
    protected function createAuthenticationInfo($type, array $options = array())
    {
        $info = $this->getMockBuilder('InAuthzWs\Client\AuthenticationInfo')
            ->disableOriginalConstructor()
            ->getMock();
        
        $info->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));
        $info->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));
        
        return $info;
    }


    protected function createClient($clientId, $authenticationInfo)
    {
        $client = $this->getMockBuilder('InAuthzWs\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        
        $client->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($clientId));
        $client->expects($this->any())
            ->method('getAuthenticationInfo')
            ->will($this->returnValue($authenticationInfo));
        
        return $client;
    }
}