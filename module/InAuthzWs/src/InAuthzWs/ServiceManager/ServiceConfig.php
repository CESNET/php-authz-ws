<?php

namespace InAuthzWs\ServiceManager;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use InAuthzWs\Handler;
use Zend\InputFilter\Factory;
use InAuthzWs\Handler\Filter\AclFilterFactory;
use InAuthzWs\Client\Registry\Registry;
use InAuthzWs\Client\Validator\Simple;
use InAuthzWs\Client\Authenticator\Secret;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            /*
             * DB adapter
             */
            'AuthzDbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory', 
            
            'AuthzLogger' => function (ServiceManager $serviceManager)
            {
                $config = $serviceManager->get('Config');
                $loggerConfig = $config['logger'];
                if (! isset($loggerConfig['writers'])) {
                    throw new Exception\MissingConfigException('logger/writers');
                }
                
                $logger = new \Zend\Log\Logger();
                
                if (count($loggerConfig['writers'])) {
                    
                    $priority = 1;
                    foreach ($loggerConfig['writers'] as $writerConfig) {
                        
                        $writer = $logger->writerPlugin($writerConfig['name'], $writerConfig['options']);
                        
                        /*
                        if (isset($writerConfig['filters']) && is_array($writerConfig['filters'])) {
                            foreach ($writerConfig['filters'] as $filterName => $filterValue) {
                                $filterClass = '\Zend\Log\Filter\\' . String::underscoreToCamelCase($filterName);
                                $filter = new $filterClass($filterValue);
                                $writer->addFilter($filter);
                            }
                        }
                        */
                        
                        if (isset($writerConfig['formatter']) && is_array($writerConfig['formatter']) &&
                             isset($writerConfig['formatter'])) {
                            $formatterConfig = $writerConfig['formatter'];
                            if (isset($formatterConfig['format'])) {
                                $formatter = new \Zend\Log\Formatter\Simple($formatterConfig['format']);
                                if (isset($formatterConfig['dateTimeFormat'])) {
                                    $formatter->setDateTimeFormat($formatterConfig['dateTimeFormat']);
                                }
                                
                                $writer->setFormatter($formatter);
                            }
                        }
                        
                        $logger->addWriter($writer, $priority ++);
                    }
                }
                
                return $logger;
            }, 
            
            /*
             * Client registry storage
             */
            'AuthzClientStorage' => function (ServiceManager $serviceManager)
            {
                $config = $serviceManager->get('Config');
                if (! isset($config['client_storage'])) {
                    throw new Exception\MissingConfigException('client_storage');
                }
                
                $storageConfig = $config['client_storage'];
                if (! isset($storageConfig['class'])) {
                    throw new Exception\MissingConfigException('client_storage/class');
                }
                $storageClass = $storageConfig['class'];
                if (!\class_exists($storageClass)) {
                    throw new Exception\ClassNotFoundException($storageClass);
                }
                
                $storageOptions = array();
                if (isset($storageConfig['options'])) {
                    $storageOptions = $storageConfig['options'];
                }
                
                $storage = new $storageClass($storageOptions);
                
                return $storage;
            }, 
            
            /*
             * Client registry
             */
            'AuthzClientRegistry' => function (ServiceManager $serviceManager)
            {
                $registry = new Registry($serviceManager->get('AuthzClientStorage'));
                
                return $registry;
            }, 
            
            'AuthzClientValidator' => function (ServiceManager $serviceManager)
            {
                return new Simple();
            }, 
            
            /*
             * Client authenticator
             */
            'AuthzClientAuthenticator' => function (ServiceManager $serviceManager)
            {
                return new Secret($serviceManager->get('AuthzClientRegistry'), $serviceManager->get('AuthzClientValidator'));
            },
            
            /*
             * Filter factory for the ACL resource handler
             */
            'AuthzAclFilterFactory' => function (ServiceManager $serviceManager)
            {
                $config = $serviceManager->get('Config');
                if (! isset($config['acl_filter_definitions'])) {
                    throw new Exception\MissingConfigException('acl_filter_definitions');
                }
                
                return new AclFilterFactory($config['acl_filter_definitions']);
            }, 
            
            /*
             * ACL resource handler
             */
            'AuthzAclHandler' => function (ServiceManager $serviceManager)
            {
                
                $handler = new Handler\Acl($serviceManager->get('AuthzDbAdapter'));
                $handler->setFilterFactory($serviceManager->get('AuthzAclFilterFactory'));
                return $handler;
            }
        );
    }
}