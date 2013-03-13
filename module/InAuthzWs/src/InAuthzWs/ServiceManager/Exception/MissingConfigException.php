<?php

namespace InAuthzWs\ServiceManager\Exception;


class MissingConfigException extends \RuntimeException
{


    public function __construct($configName)
    {
        parent::__construct(sprintf("Missing config directive '%s'", $configName));
    }
}