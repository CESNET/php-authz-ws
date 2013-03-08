<?php

namespace InAuthzWs;

use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use InAuthzWs\ServiceManager\ServiceConfig;
use InAuthzWs\ServiceManager\ControllerConfig;


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
    
    /*
    public function onBootstrap(MvcEvent $e)
    {
        //_dump($e->getApplication()->getServiceManager()->get('Di')->get('dummy_auth_adapter'));
        //_dump($e->getApplication()->getServiceManager()->get('dummy_auth_adapter'));
    }
    */
}