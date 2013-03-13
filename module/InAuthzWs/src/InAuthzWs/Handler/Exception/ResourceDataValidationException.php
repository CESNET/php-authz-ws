<?php

namespace InAuthzWs\Handler\Exception;


class ResourceDataValidationException extends \RuntimeException
{

    protected $validationMessages = array();


    public function __construct(array $messages)
    {
        $this->validationMessages = $messages;
        parent::__construct(sprintf("Invalid resource data: %s", implode(', ', array_keys($messages))));
    }


    public function getValidationMessages()
    {
        return $this->validationMessages;
    }
}