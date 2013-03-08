<?php

namespace InAuthzWs\Listener\Exception;

use InAuthzWs\Handler\ResourceHandlerInterface;


class UndefinedHandlerMethodException extends \RuntimeException
{


    public function __construct($methodName, ResourceHandlerInterface $handler)
    {
        parent::__construct(sprintf("Calling undefined method '%s' for handler '%s'", $methodName, get_class($handler)));
    }
}