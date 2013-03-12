<?php

namespace InAuthzWs\Handler;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Db\Adapter\Adapter;


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
        $select = $this->getSelect('acl');
        $select->where(array(
            'id' => $id
        ));
        
        $result = $this->executeSelect($select);
        if (! $result->count()) {
            return null;
        }
        
        return ((array) $result->current());
    }


    public function fetchAll(array $params = array())
    {
        $select = $this->getSelect('acl');
        $select->order('id ASC');
        
        $result = $this->executeSelect($select);
        
        $data = array();
        foreach ($result as $row) {
            $data[] = (array) $row;
        }
        
        //_dump($data);
        
        return $data;
        /*
        $paginator = new Paginator(new ArrayAdapter($data));
        return $paginator;
        */
    }
}
