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
            
            'InAuthzWs\AclController' => function ($controllers)
            {
                $serviceManager = $controllers->getServiceLocator();
                $events = $serviceManager->get('EventManager');
                
                try {
                    //$handler = new Handler\Acl();
                    $handler = $serviceManager->get('InAuthzWs\AclHandler');
                    
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
                    $controller->setCollectionName('acls');
                    $controller->setLogger($serviceManager->get('InAuthzWs\Logger'));
                    $controller->setClientAuthenticator($serviceManager->get('InAuthzWs\ClientAuthenticator'));
                } catch (\Exception $e) {
                    _dump("$e");
                    throw $e;
                }
                
                return $controller;
            }, 
            
            'InAuthzWs\RoleController' => function ($controllers)
            {
                $serviceManager = $controllers->getServiceLocator();
                
                $controller = new ResourceController();
                $controller->setResourceHandler($serviceManager->get('InAuthzWs\RoleHandler'));
                $controller->setRoute('authz-rest/role');
                $controller->setCollectionName('roles');
                $controller->setLogger($serviceManager->get('InAuthzWs\Logger'));
                $controller->setClientAuthenticator($serviceManager->get('InAuthzWs\ClientAuthenticator'));
                
                return $controller;
            },
            
            'InAuthzWs\PermissionController' => function ($controllers)
            {
                $serviceManager = $controllers->getServiceLocator();
                
                $controller = new ResourceController();
                $controller->setResourceHandler($serviceManager->get('InAuthzWs\PermissionHandler'));
                $controller->setRoute('authz-rest/permission');
                $controller->setCollectionName('permissions');
                $controller->setLogger($serviceManager->get('InAuthzWs\Logger'));
                $controller->setClientAuthenticator($serviceManager->get('InAuthzWs\ClientAuthenticator'));
                
                return $controller;
            }
        );
    }
}