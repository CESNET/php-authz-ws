<?php

namespace InAuthzWs\Handler\Filter;

use Zend\InputFilter\InputFilter;


abstract class AbstractFilterFactory implements FilterFactoryInterface
{

    /**
     * Filter definitions used for the InputFilter construction.
     * 
     * @var array
     */
    protected $filterDefinitions = array();


    public function __construct(array $filterDefinitions)
    {
        $this->filterDefinitions = $filterDefinitions;
    }


    public function getFilterDefinition($name)
    {
        if (isset($this->filterDefinitions[$name])) {
            return $this->filterDefinitions[$name];
        }
        
        return null;
    }


    /**
     * Creates and returns an InputFilter.
     * 
     * @return InputFilter
     */
    protected function createInputFilter()
    {
        return new InputFilter();
    }
}