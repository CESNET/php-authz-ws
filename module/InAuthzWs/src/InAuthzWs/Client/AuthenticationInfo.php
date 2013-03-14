<?php

namespace InAuthzWs\Client;


class AuthenticationInfo
{

    /**
     * Auth. type.
     * @var string
     */
    protected $type;

    /**
     * Auth. options.
     * @var array
     */
    protected $options;


    public function __construct($type, array $options = array())
    {
        $this->setType($type);
        $this->setOptions($options);
    }


    public function setType($type)
    {
        $this->type = $type;
    }


    public function getType()
    {
        return $this->type;
    }


    public function setOptions(array $options)
    {
        $this->options = $options;
    }


    public function getOptions()
    {
        return $this->options;
    }
}