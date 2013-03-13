<?php

namespace InAuthzWs\Handler;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;
use Zend\InputFilter\InputFilter;
use InAuthzWs\Handler\Filter\FilterFactoryInterface;


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

    /**
     * Filter factory.
     * 
     * @var FilterFactoryInterface
     */
    protected $filterFactory = null;


    /**
     * Constructor.
     * 
     * @param Adapter $dbAdapter
     */
    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        $this->sql = new Sql($this->dbAdapter);
    }


    /**
     * Sets the filter factory.
     * 
     * @param FilterFactoryInterface $filterFactory
     */
    public function setFilterFactory(FilterFactoryInterface $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }


    /**
     * Returns the filter factory.
     * 
     * @return FilterFactoryInterface
     */
    public function getFilterFactory()
    {
        return $this->filterFactory;
    }


    protected function applyDataFilter(array $data)
    {
        $filterFactory = $this->getFilterFactory();
        if ($filterFactory) {
            $filter = $filterFactory->createDataFilter();
            $filter->setData($data);
            
            if (! $filter->isValid()) {
                throw new Exception\ResourceDataValidationException($filter->getMessages());
            }
            
            $data = $filter->getValues();
        }
        
        return $data;
    }


    protected function applyListQueryFilter(array $data)
    {
        $filterFactory = $this->getFilterFactory();
        if ($filterFactory) {
            $filter = $filterFactory->createQueryFilter();
            $filter->setData($data);
            
            if (! $filter->isValid()) {
                throw new Exception\ResourceDataValidationException($filter->getMessages());
            }
            
            $data = $filter->getValues();
        }
        
        return $data;
    }


    /**
     * Returns the Sql object.
     * 
     * @return \Zend\Db\Sql\Sql
     */
    protected function getSql()
    {
        return $this->sql;
    }


    /**
     * Executes a Sql object and returns the result.
     * 
     * @param SqlInterface $sqlObject
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\Zend\Db\ResultSet|\Zend\Db\Adapter\Driver\ResultInterface|\Zend\Db\ResultSet\Zend\Db\ResultSetInterface>
     */
    protected function executeSqlQuery(SqlInterface $sqlObject)
    {
        $sqlString = $this->getSql()
            ->getSqlStringForSqlObject($sqlObject);
        $result = $this->dbAdapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        
        return $result;
    }


    /**
     * Returns a DB identifikator prefixed by the current table.
     *
     * @param string $name
     * @return string
     */
    protected function prefixDbName($name)
    {
        return sprintf("%s.%s", $this->dbTable, $name);
    }
}