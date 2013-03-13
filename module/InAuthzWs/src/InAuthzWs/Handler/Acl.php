<?php

namespace InAuthzWs\Handler;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory;


class Acl extends AbstractResourceHandler
{

    protected $dbTable = 'acl';


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\ResourceHandlerInterface::create()
     */
    public function create(array $data, array $params = array())
    {
        $data = $this->applyDataFilter($data);
        
        $insert = $this->getSql()
            ->insert($this->dbTable);
        $insert->values($data);
        
        $result = $this->executeSqlQuery($insert);
        $id = $result->getGeneratedValue();
        
        return $this->fetch($id);
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\ResourceHandlerInterface::update()
     */
    public function update($id, array $data, array $params = array())
    {
        $update = $this->getSql()
            ->update($this->dbTable);
        
        // To be done
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\ResourceHandlerInterface::delete()
     */
    public function delete($id, array $params = array())
    {
        $delete = $this->getSql()
            ->delete($this->dbTable);
        $delete->where(array(
            'id' => $id
        ));
        
        $result = $this->executeSqlQuery($delete);
        if (0 == $result->getAffectedRows()) {
            return false;
        }
        
        return true;
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\ResourceHandlerInterface::fetch()
     */
    public function fetch($id, array $params = array())
    {
        $select = $this->getSql()
            ->select($this->dbTable);
        $select->where(array(
            'id' => $id
        ));
        
        $result = $this->executeSqlQuery($select);
        if (! $result->count()) {
            return null;
        }
        
        return ((array) $result->current());
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\ResourceHandlerInterface::fetchAll()
     */
    public function fetchAll(array $params = array())
    {
        $params = $this->applyListQueryFilter((array) $params['query']);
        //_dump($params);
        

        $select = $this->getSql()
            ->select($this->dbTable);
        
        if (isset($params['role_id'])) {
            $select->where(array(
                $this->prefixDbName('role_id') => $params['role_id']
            ));
        }
        
        if (isset($params['user_id'])) {
            $select->where(array(
                'user_id' => $params['user_id']
            ));
        }
        
        if (isset($params['resource_id'])) {
            $select->where(array(
                'resource_id' => $params['resource_id']
            ));
        }
        
        if (isset($params['permission_id'])) {
            $select->join(array(
                'rp' => 'role_has_permission'
            ), $this->prefixDbName('role_id') . '= rp.role_id', array());
            $select->where(array(
                'permission_id' => $params['permission_id']
            ));
        }
        
        $select->order('id ASC');
        
        //_dump($select->getSqlString());
        $result = $this->executeSqlQuery($select);
        
        $items = array();
        foreach ($result as $row) {
            $resource = (array) $row;
            $items[] = $resource;
        }
        
        //_dump($data);
        

        return array(
            'items' => $items, 
            'count' => count($items), 
            'params' => $params
        );
        /*
        $paginator = new Paginator(new ArrayAdapter($data));
        return $paginator;
        */
    }
}
