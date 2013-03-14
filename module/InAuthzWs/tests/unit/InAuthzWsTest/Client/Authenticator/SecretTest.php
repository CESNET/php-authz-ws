<?php

namespace InAuthzWsTest\Client\Authenticator;

use Zend\Http\Request;
use InAuthzWs\Client\Authenticator\Secret;
use InAuthzWs\Client\Authenticator\Result;
use InAuthzWs\Client\Validator\Exception\InvalidClientException;


class SecretTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Secret
     */
    protected $authenticator = null;


    public function setUp()
    {
        /*
        $registry = $this->getMockBuilder('InAuthzWs\Client\Registry\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        */
        $registry = $this->createClientRegistry();
        $validator = $this->createValidator();
        
        $this->authenticator = new Secret($registry, $validator);
    }


    public function testGetCredentials()
    {
        $clientId = 'testclient';
        $clientSecret = 'testclientsecret';
        
        //$request = Request::fromString("GET / HTTP/1.0\r\nAuthorization: id=$clientId;secret=$clientSecret\r\n\r\n");
        $request = $this->createRequest("id=$clientId;secret=$clientSecret");
        
        $credentials = $this->authenticator->getCredentials($request);
        
        $this->assertSame($clientId, $credentials['id']);
        $this->assertSame($clientSecret, $credentials['secret']);
    }


    public function testAuthenticateErrorParsingCredentials()
    {
        $result = $this->authenticator->authenticate($this->createRequest());
        $this->assertSame(Result::CODE_FAILURE_BAD_CREDENTIALS, $result->getCode());
    }


    public function testAuthenticateIncompleteCredentials()
    {
        $request = Request::fromString($this->createRequest('foo=bar;foo1=bar1'));
        $result = $this->authenticator->authenticate($request);
        $this->assertSame(Result::CODE_FAILURE_BAD_CREDENTIALS, $result->getCode());
    }


    public function testAuthenticateClientNotFound()
    {
        $registry = $this->createClientRegistry('searchClientId');
        $validator = $this->createValidator();
        
        $authenticator = new Secret($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest('id=searchClientId;secret=foo'));
        $this->assertSame(Result::CODE_FAILURE_CLIENT_NOT_FOUND, $result->getCode());
    }


    public function testAuthenticateInvalidClient()
    {
        $client = $this->createClient();
        $registry = $this->createClientRegistry('searchClientId', $client);
        $validator = $this->createValidator(true, $client);
        
        $authenticator = new Secret($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest('id=searchClientId;secret=foo'));
        $this->assertSame(Result::CODE_FAILURE_CLIENT_CONFIG, $result->getCode());
    }


    public function testAuthenticateInvalidCredentials()
    {
        $clientId = "searchClientId";
        $secret = 'foo';
        $authOptions = array(
            'secret' => $secret
        );
        
        $client = $this->createClient($authOptions);
        $registry = $this->createClientRegistry($clientId, $client);
        $validator = $this->createValidator();
        
        $authenticator = new Secret($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest("id=$clientId;secret=bar"));
        $this->assertSame(Result::CODE_FAILURE_INVALID_CREDENTIALS, $result->getCode());
    }


    public function testAuthenticateSuccess()
    {
        $clientId = "searchClientId";
        $secret = 'foo';
        $authOptions = array(
            'secret' => $secret
        );
        
        $client = $this->createClient($authOptions);
        $registry = $this->createClientRegistry($clientId, $client);
        $validator = $this->createValidator();
        
        $authenticator = new Secret($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest("id=$clientId;secret=$secret"));
        $this->assertSame(Result::CODE_SUCCESS, $result->getCode());
    }
    
    //--------------------
    protected function createRequest($authorizationValue = null)
    {
        $requestString = "GET / HTTP/1.0\r\n";
        if ($authorizationValue) {
            $requestString .= "Authorization: $authorizationValue\r\n";
        }
        $requestString .= "\r\n";
        
        return Request::fromString($requestString);
    }


    protected function createClient(array $authOptions = null)
    {
        $authenticationInfo = $this->createAuthenticationInfo($authOptions);
        
        $client = $this->getMockBuilder('InAuthzWs\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('getAuthenticationInfo')
            ->will($this->returnValue($authenticationInfo));
        
        return $client;
    }


    protected function createAuthenticationInfo(array $options = null)
    {
        $authenticationInfo = $this->getMockBuilder('InAuthzWs\Client\AuthenticationInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $authenticationInfo->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));
        
        return $authenticationInfo;
    }


    protected function createClientRegistry($clientId = null, $client = null)
    {
        $registry = $this->getMockBuilder('InAuthzWs\Client\Registry\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        
        if ($clientId) {
            $registry->expects($this->once())
                ->method('getClientById')
                ->with($clientId)
                ->will($this->returnValue($client));
        }
        
        return $registry;
    }


    protected function createValidator($invalid = false, $client = null)
    {
        $validator = $this->getMock('InAuthzWs\Client\Validator\ValidatorInterface');
        if ($invalid) {
            $validator->expects($this->once())
                ->method('validate')
                ->with($client)
                ->will($this->throwException(new InvalidClientException('invalid client')));
        }
        return $validator;
    }
}