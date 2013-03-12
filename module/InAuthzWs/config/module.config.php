<?php
return array(
    
    'router' => array(
        'routes' => array(
            'authz-rest' => array(
                'type' => 'Literal', 
                'options' => array(
                    'route' => '/rest'
                ), 
                
                'child_routes' => array(
                    
                    'acl' => array(
                        'type' => 'Segment', 
                        'may_terminate' => true, 
                        'options' => array(
                            'route' => '/acl[/:id]', 
                            //'route' => '/acl[/:id][/:subresource][/:subresourceId]',
                            'constraints' => array(
                                'id' => '[\w-]+'
                            ), 
                            'defaults' => array(
                                'controller' => 'AclController'
                            )
                        )
                    )
                )
            )
        )
    ), 
    
    'db' => array(
        'driver' => 'Pdo_Mysql', 
        'host' => 'localhost', 
        'dbname' => 'authz_ws', 
        'username' => 'authzadmin', 
        'password' => ''
    )
);