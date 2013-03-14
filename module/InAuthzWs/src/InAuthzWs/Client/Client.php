<?php

namespace InAuthzWs\Client;


class Client
{

    /**
     * Client ID.
     * 
     * @var string
     */
    protected $id;

    /**
     * Client description.
     * 
     * @var string
     */
    protected $description;

    /**
     * Client authentication info.
     * 
     * @var AuthenticationInfo
     */
    protected $authenticationInfo;


    public function __construct($id, $description = '', AuthenticationInfo $authenticationInfo = null)
    {
        $this->setId($id);
        $this->setDescription($description);
        if (null !== $authenticationInfo) {
            $this->setAuthenticationInfo($authenticationInfo);
        }
    }


    public function setId($id)
    {
        $this->id = $id;
    }


    public function getId()
    {
        return $this->id;
    }


    public function setDescription($description)
    {
        $this->description = $description;
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function setAuthenticationInfo(AuthenticationInfo $authenticationInfo)
    {
        $this->authenticationInfo = $authenticationInfo;
    }


    /**
     * Returns the client's authentication info.
     * 
     * @return AuthenticationInfo
     */
    public function getAuthenticationInfo()
    {
        return $this->authenticationInfo;
    }
}