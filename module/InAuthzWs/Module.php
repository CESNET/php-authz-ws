<?php

namespace InAuthzWs;

use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use InAuthzWs\ServiceManager\ServiceConfig;
use InAuthzWs\ServiceManager\ControllerConfig;
use Zend\Mvc\MvcEvent;


class Module implements AutoloaderProviderInterface, ControllerProviderInterface
{


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getControllerConfig()
    {
        return new ControllerConfig();
    }


    public function getServiceConfig()
    {
        return new ServiceConfig();
    }


    public function onBootstrap(MvcEvent $e)
    {
        $events = $e->getApplication()
            ->getEventManager();
        
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach('InAuthzWs\Controller\ResourceController', 'dispatch', function ($e)
        {
            $eventManager = $e->getApplication()
                ->getEventManager();
            $serviceManager = $e->getApplication()
                ->getServiceManager();
            $eventManager->attach($serviceManager->get('InAuthzWs\ApiProblemListener'));
            $eventManager->attach($serviceManager->get('InAuthzWs\DispatchErrorListener'));
        }, 300);
    }
}