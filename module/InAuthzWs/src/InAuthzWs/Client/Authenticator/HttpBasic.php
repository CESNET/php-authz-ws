<?php

namespace InAuthzWs\Client\Authenticator;

use InAuthzWs\Client\Registry\Registry;
use Zend\Http\Request;
use InAuthzWs\Client\Validator\ValidatorInterface;


class HttpBasic implements AuthenticatorInterface
{

    /**
     * Client registry.
     *
     * @var Registry
     */
    protected $clientRegistry = null;

    /**
     * CLient validator.
     *
     * @var ValidatorInterface
     */
    protected $clientValidator = null;


    /**
     * Constructor.
     *
     * @param Registry $clientRegistry
     * @param ValidatorInterface $clientValidator
     */
    public function __construct(Registry $clientRegistry, ValidatorInterface $clientValidator)
    {
        $this->clientRegistry = $clientRegistry;
        $this->clientValidator = $clientValidator;
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Client\Authenticator\AuthenticatorInterface::authenticate()
     */
    public function authenticate(Request $request)
    {
        $result = null;
        
        try {
            $credentials = $this->getCredentials($request);
        } catch (\Exception $e) {
            return Result::createFailure(
                array(
                    'Error parsing credentials: ' . $e->getMessage()
                ), Result::CODE_FAILURE_BAD_CREDENTIALS);
        }
        
        if (! isset($credentials['id']) || ! isset($credentials['secret'])) {
            return Result::createFailure(array(
                'Incomplete credentials'
            ), Result::CODE_FAILURE_BAD_CREDENTIALS);
        }
        
        $id = $credentials['id'];
        $secret = $credentials['secret'];
        
        $client = $this->clientRegistry->getClientById($id);
        if (null === $client) {
            return Result::createFailure(array(
                sprintf("Invalid client ID: '%s'", $id)
            ), Result::CODE_FAILURE_CLIENT_NOT_FOUND);
        }
        
        try {
            $this->clientValidator->validate($client);
        } catch (\Exception $e) {
            return Result::createFailure(array(
                $e->getMessage()
            ), Result::CODE_FAILURE_CLIENT_CONFIG);
        }
        
        $options = $client->getAuthenticationInfo()->getOptions();
        
        if ($secret !== $options['secret']) {
            return Result::createFailure(
                array(
                    sprintf("Invalid credentials for client '%s'", $id)
                ), Result::CODE_FAILURE_INVALID_CREDENTIALS);
        }
        
        return Result::createSuccess($client);
    }


    /**
     * Parses the request and extracts the client credentials.
     *
     * @param Request $request
     * @throws Exception\BadCredentialsException
     */
    public function getCredentials(Request $request)
    {
        $authHeader = $request->getHeader('Authorization');
        if (! $authHeader) {
            throw new Exception\BadCredentialsException('Missing header');
        }
        
        $parts = explode(' ', $authHeader->getFieldValue(), 2);
        if (2 != count($parts)) {
            throw new Exception\BadCredentialsException('Invalid field format');
        }
        
        if ('basic' !== trim(strtolower($parts[0]))) {
            throw new Exception\BadCredentialsException('Invalid authentication type');
        }
        
        $encodedString = trim($parts[1]);
        
        $decodedString = base64_decode($encodedString, true);
        if (false === $decodedString) {
            throw new Exception\BadCredentialsException('Error decoding credentials');
        }
        
        $parts = explode(':', $decodedString, 2);
        if (2 != count($parts)) {
            throw new Exception\BadCredentialsException('Invalid credential format');
        }
        
        return array(
            'id' => trim($parts[0]),
            'secret' => trim($parts[1])
        );
    }


    protected function isValidFieldString($fieldString)
    {
        return preg_match('/^\w+$/', $fieldString);
    }
}