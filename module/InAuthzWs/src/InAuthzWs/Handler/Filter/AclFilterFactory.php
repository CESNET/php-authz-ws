<?php

namespace InAuthzWs\Handler\Filter;


class AclFilterFactory extends AbstractFilterFactory
{

    protected $dataFilters = array(
        'user_id', 
        'role_id', 
        'resource_id'
    );

    protected $queryFilters = array(
        'user_id', 
        'role_id', 
        'resource_id', 
        'permission_id'
    );


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\Filter\FilterFactoryInterface::createDataFilter()
     */
    public function createDataFilter()
    {
        $filter = $this->createInputFilter();
        
        foreach ($this->dataFilters as $name) {
            $filterDefinition = $this->getFilterDefinition($name);
            if (null === $filterDefinition || ! is_array($filterDefinition)) {
                throw new Exception\MissingFilterDefinitionException($name);
            }
            
            $filter->add($filterDefinition);
        }
        
        return $filter;
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Handler\Filter\FilterFactoryInterface::createQueryFilter()
     */
    public function createQueryFilter()
    {
        $filter = $this->createInputFilter();
        
        foreach ($this->queryFilters as $name) {
            $filterDefinition = $this->getFilterDefinition($name);
            if (null === $filterDefinition || ! is_array($filterDefinition)) {
                throw new Exception\MissingFilterDefinitionException($name);
            }
            
            $filterDefinition['required'] = false;
            
            $filter->add($filterDefinition);
        }
        
        return $filter;
    }
}