<?php

namespace InAuthzWs\Listener;

use InAuthzWs\Handler\ResourceHandlerInterface;


interface ResourceListenerInterface
{


    /**
     * Sets the resource handler.
     * 
     * @param ResourceHandlerInterface $handler
     */
    public function setResourceHandler(ResourceHandlerInterface $handler);


    /**
     * Returns the resource handler.
     * 
     * @return ResourceHandlerInterface
     */
    public function getResourceHandler();
}

