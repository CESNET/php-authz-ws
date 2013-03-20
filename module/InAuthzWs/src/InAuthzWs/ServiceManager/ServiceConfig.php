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
use InAuthzWs\Listener\ApiProblemListener;
use InAuthzWs\Listener\DispatchErrorListener;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            'InAuthzWs\ApiProblemListener' => function ($services)
            {
                $config = array();
                if ($services->has('config')) {
                    $config = $services->get('config');
                }
                
                $filter = null;
                if (isset($config['phlyrestfully']) && isset($config['phlyrestfully']['accept_filter'])) {
                    $filter = $config['phlyrestfully']['accept_filter'];
                }
                
                return new ApiProblemListener($filter);
            }, 
            
            'InAuthzWs\DispatchErrorListener' => function (ServiceManager $serviceManager)
            {
                return new DispatchErrorListener($serviceManager->get('InAuthzWs\Logger'));
            },
            
            /*
             * DB adapter
             */
            'InAuthzWs\DbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory', 
            
            'InAuthzWs\Logger' => function (ServiceManager $serviceManager)
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
            'InAuthzWs\ClientStorage' => function (ServiceManager $serviceManager)
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
                if (! \class_exists($storageClass)) {
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
            'InAuthzWs\ClientRegistry' => function (ServiceManager $serviceManager)
            {
                $registry = new Registry($serviceManager->get('InAuthzWs\ClientStorage'));
                
                return $registry;
            }, 
            
            'InAuthzWs\ClientValidator' => function (ServiceManager $serviceManager)
            {
                return new Simple();
            }, 
            
            /*
             * Client authenticator
             */
            'InAuthzWs\ClientAuthenticator' => function (ServiceManager $serviceManager)
            {
                return new Secret($serviceManager->get('InAuthzWs\ClientRegistry'), $serviceManager->get('InAuthzWs\ClientValidator'));
            },
            
            /*
             * Filter factory for the ACL resource handler
             */
            'InAuthzWs\AclFilterFactory' => function (ServiceManager $serviceManager)
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
            'InAuthzWs\AclHandler' => function (ServiceManager $serviceManager)
            {
                
                $handler = new Handler\Acl($serviceManager->get('InAuthzWs\DbAdapter'));
                $handler->setFilterFactory($serviceManager->get('InAuthzWs\AclFilterFactory'));
                return $handler;
            }, 
            
            /*
             * Role resource handler
             */
            'InAuthzWs\RoleHandler' => function (ServiceManager $serviceManager)
            {
                $handler = new Handler\Role($serviceManager->get('InAuthzWs\DbAdapter'));
                $handler->setPermissionHandler($serviceManager->get('InAuthzWs\PermissionHandler'));
                
                return $handler;
            },
            
            /*
             * Permission resource handler
             */
            'AInAuthzWs\PermissionHandler' => function (ServiceManager $serviceManager)
            {
                $handler = new Handler\Permission($serviceManager->get('InAuthzWs\DbAdapter'));
                
                return $handler;
            }
        );
    }
}