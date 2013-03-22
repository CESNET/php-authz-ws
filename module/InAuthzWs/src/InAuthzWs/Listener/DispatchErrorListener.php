<?php

namespace InAuthzWs\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Log\LoggerInterface;
use Zend\View\Model\ModelInterface;


class DispatchErrorListener implements ListenerAggregateInterface
{

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Logger.
     * 
     * @var Logger
     */
    protected $logger = null;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, __CLASS__ . '::onRender', 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array(
            $this, 
            'onRender'
        ), 1100);
    }


    /**
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function onRender(MvcEvent $e)
    {
        if (! $e->isError()) {
            return;
        }
        
        $model = $e->getResult();
        if (! $model instanceof ModelInterface) {
            return;
        }
        
        $exception = $model->getVariable('exception');
        
        if ($exception instanceof \Exception) {
            $this->logger->err(sprintf("Exception [%s]: %s", get_class($exception), $exception->getMessage()));
            $this->logger->debug($exception->getTraceAsString());
        } else {
            $this->logger->err($model->getVariable('message') . '[' . $_SERVER['REQUEST_URI'] . ']');
        }
    }
}