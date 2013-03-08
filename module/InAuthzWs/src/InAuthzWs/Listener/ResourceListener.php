<?php

namespace InAuthzWs\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use InAuthzWs\Handler\ResourceHandlerInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;


class ResourceListener implements ListenerAggregateInterface, ResourceListenerInterface
{

    protected $resourceHandler = null;

    protected $listeners = array();

    protected $eventList = array(
        'fetch', 
        'fetchAll', 
        'create', 
        'update', 
        'delete'
    );


    public function __construct(ResourceHandlerInterface $handler)
    {
        $this->setResourceHandler($handler);
    }


    public function attach(EventManagerInterface $eventManager)
    {
        foreach ($this->eventList as $eventName) {
            $this->listeners[] = $eventManager->attach($eventName, array(
                $this, 
                $eventName
            ));
        }
    }


    public function detach(EventManagerInterface $eventManager)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($eventManager->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function setResourceHandler(ResourceHandlerInterface $handler)
    {
        $this->resourceHandler = $handler;
    }


    public function getResourceHandler()
    {
        return $this->resourceHandler;
    }


    public function fetch(Event $event)
    {
        return $this->callHandlerMethod(__FUNCTION__, array(
            $event->getParam('id', null), 
            $event->getParam('params', array())
        ));
    }


    public function fetchAll(Event $event)
    {
        return $this->callHandlerMethod(__FUNCTION__, array(
            $event->getParam('params', array())
        ));
    }


    public function create(Event $event)
    {
        return $this->callHandlerMethod(__FUNCTION__, array(
            $event->getParam('data', array()), 
            $event->getParam('params', array())
        ));
    }


    public function update(Event $event)
    {
        return $this->callHandlerMethod(__FUNCTION__, array(
            $event->getParam('id', null), 
            $event->getParam('data', array()), 
            $event->getParam('params', array())
        ));
    }


    public function delete(Event $event)
    {
        return $this->callHandlerMethod(__FUNCTION__, array(
            $event->getParam('id', null), 
            $event->getParam('params', array())
        ));
    }


    protected function callHandlerMethod($methodName, array $params)
    {
        if (! method_exists($this->resourceHandler, $methodName)) {
            throw new Exception\UndefinedHandlerMethodException($methodName, $this->resourceHandler);
        }
        
        return call_user_func_array(array(
            $this->resourceHandler, 
            $methodName
        ), $params);
    }
}