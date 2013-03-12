<?php

namespace InAuthzWs\ServiceManager;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use InAuthzWs\Handler;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            'AuthzDbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory', 
            
            'AuthzHandlerAcl' => function (ServiceManager $serviceManager)
            {
                
                return new Handler\Acl($serviceManager->get('AuthzDbAdapter'));
            }
        );
    }
}