<?php

namespace InAuthzWs\Handler;

use PhlyRestfully\ApiProblem;
use PhlyRestfully\HalCollection;
use PhlyRestfully\HalResource;


class Role extends AbstractResourceHandler
{

    protected $dbTable = 'role';

    /**
     * Permission resource handler.
     * 
     * @var Permission
     */
    protected $permissionHandler = null;


    public function setPermissionHandler(Permission $permissionHandler)
    {
        $this->permissionHandler = $permissionHandler;
    }


    public function create(array $data, array $params = array())
    {}


    public function update($id, array $data, array $params = array())
    {}


    public function delete($id, array $params = array())
    {}


    public function fetch($id, array $params = array())
    {
        $select = $this->getSql()
            ->select($this->dbTable);
        
        //_dump($select->getSqlString());
        $select->where(array(
            'id' => $id
        ));
        
        $result = $this->executeSqlQuery($select);
        
        if (! $result->count()) {
            return null;
        }
        
        $row = ((array) $result->current());
        
        $data = array(
            'id' => $row['id'], 
            'code' => $row['code'], 
            'description' => $row['description']
        );
        
        if ($this->permissionHandler) {
            $permissions = $this->permissionHandler->fetchAll(array(
                'query' => array(
                    'role_id' => $id
                )
            ));
            
            $data['_embedded']['permissions'] = $permissions['items'];
        }
        
        return $data;
    }


    public function fetchAll(array $params = array())
    {
        $select = $this->getSql()
            ->select($this->dbTable);
        
        $select->order('id ASC');
        
        $result = $this->executeSqlQuery($select);
        
        $items = array();
        foreach ($result as $row) {
            $resource = (array) $row;
            
            $items[] = $resource;
        }
        
        return array(
            'items' => $items, 
            'count' => count($items)
        );
    }
}