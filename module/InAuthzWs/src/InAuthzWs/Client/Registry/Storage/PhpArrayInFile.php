<?php

namespace InAuthzWs\Client\Registry\Storage;

use InGeneral\Util\Options;
use InGeneral\Exception\MissingOptionException;


class PhpArrayInFile implements StorageInterface
{

    const OPT_FILE = 'file';

    /**
     * @var Options
     */
    protected $options = null;


    public function __construct(array $options)
    {
        $this->options = new Options($options);
    }


    /**
     * {@inheritdoc}
     * @see \InAuthzWs\Client\Registry\Storage\StorageInterface::loadData()
     */
    public function loadData()
    {
        $filename = $this->options->get(self::OPT_FILE);
        if (null === $filename) {
            throw new MissingOptionException(self::OPT_FILE);
        }
        
        if (! file_exists($filename)) {
            throw new Exception\LoadDataException(sprintf("Non-existent file '%s'", $filename));
        }
        
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new Exception\LoadDataException(sprintf("Invalid file '%s'", $filename));
        }
        
        $data = require $filename;
        
        if (! is_array($data)) {
            throw new Exception\LoadDataException(sprintf("Invalid data in file '%s'", $filename));
        }
        
        return $data;
    }
}