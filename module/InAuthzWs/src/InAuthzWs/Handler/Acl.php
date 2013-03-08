<?php

namespace InAuthzWs\Handler;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;


class Acl extends AbstractResourceHandler
{


    public function create(array $data, array $params = array())
    {}


    public function update($id, array $data, array $params = array())
    {}


    public function delete($id, array $params = array())
    {}


    public function fetch($id, array $params = array())
    {
        return array(
            'id' => 1, 
            'name' => 'test'
        );
    }


    public function fetchAll(array $params = array())
    {
        _dump($params);
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
        
        return $data;
        /*
        $paginator = new Paginator(new ArrayAdapter($data));
        return $paginator;
        */
    }
}
