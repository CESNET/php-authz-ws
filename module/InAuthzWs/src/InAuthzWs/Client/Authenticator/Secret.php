<?php

namespace InAuthzWs\Client\Authenticator;

use InAuthzWs\Client\Registry\Registry;
use Zend\Http\Request;
use InAuthzWs\Client\Validator\ValidatorInterface;


class Secret implements AuthenticatorInterface
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
            return Result::createFailure(array(
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
        
        $options = $client->getAuthenticationInfo()
            ->getOptions();
        
        if ($secret !== $options['secret']) {
            return Result::createFailure(array(
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
        
        $authString = $authHeader->getFieldValue();
        
        // FIXME - move to separate object.
        $fields = explode(';', $authString);
        if (count($fields) != 2) {
            throw new Exception\BadCredentialsException('Wrong field count');
        }
        
        $credentials = array();
        foreach ($fields as $field) {
            $parts = explode('=', $field);
            if (count($parts) != 2) {
                throw new Exception\BadCredentialsException('Invalid field format');
            }
            
            $fieldName = trim($parts[0]);
            $fieldValue = trim($parts[1]);
            
            if (! $this->isValidFieldString($fieldName) || ! $this->isValidFieldString($fieldValue)) {
                throw new Exception\BadCredentialsException('The credentials contain invalid characters');
            }
            
            $credentials[$fieldName] = $fieldValue;
        }
        
        return $credentials;
    }


    protected function isValidFieldString($fieldString)
    {
        return preg_match('/^\w+$/', $fieldString);
    }
}