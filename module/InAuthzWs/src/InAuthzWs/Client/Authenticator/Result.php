<?php

namespace InAuthzWs\Client\Authenticator;

use InAuthzWs\Client\Client;


class Result
{

    const CODE_SUCCESS = 'success';

    const CODE_FAILURE = 'failure';

    const CODE_FAILURE_BAD_CREDENTIALS = 'failure_bad_credentials';

    const CODE_FAILURE_CLIENT_NOT_FOUND = 'failure_client_not_found';

    const CODE_FAILURE_CLIENT_CONFIG = 'failure_client_config';

    const CODE_FAILURE_INVALID_CREDENTIALS = 'failure_invalid_credentials';

    /**
     * Authentication result code.
     * 
     * @var string
     */
    protected $code = null;

    /**
     * Authenticated client instance.
     * 
     * @var Client
     */
    protected $client = null;

    /**
     * Authentication messages.
     * 
     * @var array
     */
    protected $messages = array();


    /**
     * Constructor.
     * 
     * @param string $code
     * @param Client $client
     * @param array $messages
     */
    public function __construct($code, Client $client = null, array $messages = array())
    {
        $this->code = $code;
        
        if (null !== $client && $code === self::CODE_SUCCESS) {
            $this->client = $client;
        }
        $this->messages = $messages;
    }


    /**
     * Convenience "contructor" for creating success results.
     * 
     * @param Client $client
     * @param array $messages
     * @return Result
     */
    static public function createSuccess(Client $client, array $messages = array())
    {
        return new self(self::CODE_SUCCESS, $client, $messages);
    }


    /**
     * Convenient "constructor" for creating failure results'.
     * 
     * @param array $messages
     * @return Result
     */
    static public function createFailure(array $messages, $code = self::CODE_FAILURE)
    {
        return new self($code, null, $messages);
    }


    /**
     * Returns true, if the authentication is successful.
     * 
     * @return boolean
     */
    public function isValid()
    {
        return (self::CODE_SUCCESS === $this->code);
    }


    /**
     * Returns the result code.
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * Returns the authenticated client instance.
     * 
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }


    /**
     * Returns the authentication messages.
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}