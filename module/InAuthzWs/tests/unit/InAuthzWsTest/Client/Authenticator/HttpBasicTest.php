<?php

namespace InAuthzWsTest\Client\Authenticator;

use Zend\Http\Request;
use InAuthzWs\Client\Authenticator\HttpBasic;
use InAuthzWs\Client\Authenticator\Result;
use InAuthzWs\Client\Validator\Exception\InvalidClientException;


class HttpBasicTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Secret
     */
    protected $authenticator = null;


    public function setUp()
    {
        $registry = $this->createClientRegistry();
        $validator = $this->createValidator();
        
        $this->authenticator = new HttpBasic($registry, $validator);
    }


    public function testGetCredentials()
    {
        $clientId = 'testclient';
        $clientSecret = 'testclientsecret';
        
        // $request = Request::fromString("GET / HTTP/1.0\r\nAuthorization: id=$clientId;secret=$clientSecret\r\n\r\n");
        $request = $this->createRequest($clientId, $clientSecret);
        
        $credentials = $this->authenticator->getCredentials($request);
        
        $this->assertSame($clientId, $credentials['id']);
        $this->assertSame($clientSecret, $credentials['secret']);
    }


    public function testGetCredentialsWithMissingHeader()
    {
        $this->setExpectedException('InAuthzWs\Client\Authenticator\Exception\BadCredentialsException', 
            'Missing header');
        
        $request = $this->createRequest();
        $this->authenticator->getCredentials($request);
    }
    
    
    public function testGetCredentialsWithInvalidFieldFormat()
    {
        $this->setExpectedException('InAuthzWs\Client\Authenticator\Exception\BadCredentialsException',
            'Invalid field format');
    
        $request = $this->createRequest(null, null, 'foo');
        $this->authenticator->getCredentials($request);
    }
    
    public function testGetCredentialsWithInvalidAuthType()
    {
        $this->setExpectedException('InAuthzWs\Client\Authenticator\Exception\BadCredentialsException',
            'Invalid authentication type');
    
        $request = $this->createRequest(null, null, 'foo bar');
        $this->authenticator->getCredentials($request);
    }
    
    public function testGetCredentialsWithDecodeError()
    {
        $this->setExpectedException('InAuthzWs\Client\Authenticator\Exception\BadCredentialsException',
            'Error decoding credentials');
    
        $request = $this->createRequest(null, null, 'basic bar;');
        $this->authenticator->getCredentials($request);
    }

    
    public function testGetCredentialsWithInvalidCredentialFormat()
    {
        $this->setExpectedException('InAuthzWs\Client\Authenticator\Exception\BadCredentialsException',
            'Invalid credential format');
    
        $request = $this->createRequest(null, null, 'basic bar');
        $this->authenticator->getCredentials($request);
    }
    
    public function testAuthenticateClientNotFound()
    {
        $registry = $this->createClientRegistry('searchClientId');
        $validator = $this->createValidator();
        
        $authenticator = new HttpBasic($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest('searchClientId', 'foo'));
        $this->assertSame(Result::CODE_FAILURE_CLIENT_NOT_FOUND, $result->getCode());
    }


    public function testAuthenticateInvalidClient()
    {
        $client = $this->createClient();
        $registry = $this->createClientRegistry('searchClientId', $client);
        $validator = $this->createValidator(true, $client);
        
        $authenticator = new HttpBasic($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest('searchClientId', 'foo'));
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
        
        $authenticator = new HttpBasic($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest($clientId, 'bar'));
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
        
        $authenticator = new HttpBasic($registry, $validator);
        $result = $authenticator->authenticate($this->createRequest($clientId, $secret));
        $this->assertSame(Result::CODE_SUCCESS, $result->getCode());
    }
    
    // --------------------
    protected function createRequest($clientId = null, $clientSecret = null, $rawAuthString = null)
    {
        $requestString = "GET / HTTP/1.0\r\n";
        if ($clientId && $clientSecret) {
            $rawAuthString = 'Basic ' . base64_encode("$clientId:$clientSecret");
        }
        
        if ($rawAuthString) {
            $requestString .= "Authorization: $rawAuthString\r\n";
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