<?php

namespace InAuthzWs\Handler\Filter\Exception;


class MissingFilterDefinitionException extends \RuntimeException
{


    public function __construct($filterName)
    {
        parent::__construct(sprintf("Missing required filter definition '%s'", $filterName));
    }
}