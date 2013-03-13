<?php

namespace InAuthzWs\ServiceManager;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use InAuthzWs\Handler;
use Zend\InputFilter\Factory;
use InAuthzWs\Handler\Filter\AclFilterFactory;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            'AuthzDbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory', 
            
            'AuthzAclFilterFactory' => function (ServiceManager $serviceManager)
            {
                $config = $serviceManager->get('Config');
                if (! isset($config['acl_filter_definitions'])) {
                    throw new Exception\MissingConfigException('acl_filter_definitions');
                }
                
                return new AclFilterFactory($config['acl_filter_definitions']);
            }, 
            
            'AuthzAclHandler' => function (ServiceManager $serviceManager)
            {
                
                $handler = new Handler\Acl($serviceManager->get('AuthzDbAdapter'));
                //$handler->setFilter($serviceManager->get('AuthzFilterAcl'));
                $handler->setFilterFactory($serviceManager->get('AuthzAclFilterFactory'));
                return $handler;
            }
        );
    }
}