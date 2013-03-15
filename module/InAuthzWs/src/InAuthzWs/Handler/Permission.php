<?php

namespace InAuthzWs\Handler;


class Permission extends AbstractResourceHandler
{

    protected $dbTable = 'permission';


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
        //dump($row);
        return array(
            'id' => $row['id'], 
            'code' => $row['code'], 
            'description' => $row['description']
        );
    }


    public function fetchAll(array $params = array())
    {
        // FIXME
        $params = $params['query'];
        
        $select = $this->getSql()
            ->select($this->dbTable);
        
        if (isset($params['role_id'])) {
            $select->join(array(
                'rhp' => 'role_has_permission'
            ), $this->prefixDbName('id') . '=rhp.permission_id', array());
            
            $select->where(array(
                'rhp.role_id' => $params['role_id']
            ));
        }
        
        $select->order('id ASC');
        
        $result = $this->executeSqlQuery($select);
        
        $items = array();
        foreach ($result as $row) {
            $resource = (array) $row;
            $items[] = $resource;
        }
        
        //_dump($data);
        

        return array(
            'items' => $items, 
            'count' => count($items)
        );
    }
}