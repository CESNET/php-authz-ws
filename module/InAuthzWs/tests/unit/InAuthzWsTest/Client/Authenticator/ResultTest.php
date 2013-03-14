<?php

namespace InAuthzWsTest\Client\Authenticator;

use InAuthzWs\Client\Authenticator\Result;


class ResultTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructorFailure()
    {
        $code = 'testcode';
        $client = $this->getMockBuilder('InAuthzWs\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $messages = array(
            array(
                'message1'
            )
        );
        
        $result = new Result($code, $client, $messages);
        $this->assertSame($code, $result->getCode());
        $this->assertNull($result->getClient());
        $this->assertSame($messages, $result->getMessages());
    }


    public function testConstructorSuccess()
    {
        $code = Result::CODE_SUCCESS;
        $client = $this->getMockBuilder('InAuthzWs\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $messages = array(
            array(
                'message1'
            )
        );
        
        $result = new Result($code, $client, $messages);
        $this->assertSame($code, $result->getCode());
        $this->assertSame($client, $result->getClient());
        $this->assertSame($messages, $result->getMessages());
    }


    public function testIsValidInvalid()
    {
        $result = new Result('someresult');
        $this->assertFalse($result->isValid());
    }


    public function testIsValidValid()
    {
        $result = new Result(Result::CODE_SUCCESS);
        $this->assertTrue($result->isValid());
    }
}