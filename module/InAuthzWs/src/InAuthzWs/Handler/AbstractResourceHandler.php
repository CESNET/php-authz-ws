<?php

namespace InAuthzWs\Handler;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;


abstract class AbstractResourceHandler implements ResourceHandlerInterface
{

    /**
     * DB adapter.
     * 
     * @var Adapter
     */
    protected $dbAdapter = null;

    /**
     * 
     * @var Sql
     */
    protected $sql = null;


    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        $this->sql = new Sql($this->dbAdapter);
    }


    protected function getSql()
    {
        return $this->sql;
    }


    protected function getSelect($table = null)
    {
        return $this->getSql()
            ->select($table);
    }


    protected function executeSelect(Select $select)
    {
        $selectString = $this->getSql()
            ->getSqlStringForSqlObject($select);
        return $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
    }
}