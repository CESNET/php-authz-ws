<?php

namespace InAuthzWs\ResourceListener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use PhlyRestfully\HalCollection;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;


class Acl implements ListenerAggregateInterface
{

    protected $listeners = array();


    public function attach(EventManagerInterface $eventManager)
    {
        $this->listeners[] = $eventManager->attach('fetchAll', array(
            $this, 
            'fetchAll'
        ));
        
        $this->listeners[] = $eventManager->attach('fetch', array(
            $this,
            'fetch'
        ));
    }


    public function detach(EventManagerInterface $eventManager)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($eventManager->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function fetchAll(Event $event)
    {
        //_dump($event);
        $data = array(
            array(
                'id' => 1, 
                'name' => 'foo'
            ), 
            array(
                'id' => 2, 
                'name' => 'bar'
            )
        );
        
        $paginator = new Paginator(new ArrayAdapter($data));
        //_dump($paginator);
        return $paginator;
        
        return $data;
    }


    public function fetch(Event $event)
    {
        _dump($event->getParams());
        return array(
            'id' => 3, 
            'name' => 'test'
        );
    }
}