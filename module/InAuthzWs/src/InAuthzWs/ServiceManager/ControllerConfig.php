<?php

namespace InAuthzWs\ServiceManager;

use PhlyRestfully;
use Zend\ServiceManager\Config;
use InAuthzWs;
use InAuthzWs\Listener;
use InAuthzWs\Handler;
use InAuthzWs\ResourceListener;
use InAuthzWs\Controller\ResourceController;


class ControllerConfig extends Config
{


    public function getFactories()
    {
        return array(
            
            'AclController' => function ($controllers)
            {
                $services = $controllers->getServiceLocator();
                $events = $services->get('EventManager');
                
                try {
                    //$handler = new Handler\Acl();
                    $handler = $services->get('AuthzAclHandler');
                    
                    /*
                    $listener = new Listener\ResourceListener($handler);
                    
                    $events->attach($listener);
                    
                    $resource = new PhlyRestfully\Resource();
                    $resource->setEventManager($events);
                    _dump($resource->getEventManager()->getIdentifiers());
                    $controller = new PhlyRestfully\ResourceController();
                    $controller->setResource($resource);
                    $controller->setRoute('authz-rest/acl');
                    $controller->setPageSize(10);
                    
                    $controller->setCollectionHttpOptions(array(
                        'GET'
                    ));
                    $controller->setResourceHttpOptions(array(
                        'GET', 
                        'POST', 
                        'DELETE'
                    ));
                    */
                    
                    $controller = new ResourceController();
                    $controller->setResourceHandler($handler);
                    $controller->setRoute('authz-rest/acl');
                    $controller->setLogger($services->get('AuthzLogger'));
                } catch (\Exception $e) {
                    _dump("$e");
                    throw $e;
                }
                
                return $controller;
            }
        );
    }
}