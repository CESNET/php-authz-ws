<?php

namespace InAuthzWsTest\Client;

use InAuthzWs\Client\AuthenticationInfo;


class AuthenticationInfoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AuthenticationInfo
     */
    protected $info = null;


    public function setUp()
    {
        $this->info = new AuthenticationInfo('testtype');
    }


    public function testSetType()
    {
        $type = 'sometype';
        $this->info->setType($type);
        $this->assertSame($type, $this->info->getType());
    }


    public function testSetOptions()
    {
        $options = array(
            'foo' => 'bar'
        );
        $this->info->setOptions($options);
        $this->assertSame($options, $this->info->getOptions());
    }
}