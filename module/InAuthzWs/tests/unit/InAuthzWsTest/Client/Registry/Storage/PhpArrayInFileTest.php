<?php

namespace InAuthzWsTest\Client\Registry\Storage;

use InAuthzWs\Client\Registry\Storage\PhpArrayInFile;


class PhpArrayInFileTest extends \PHPUnit_Framework_TestCase
{


    public function testLoadDataWithNoFileOption()
    {
        $this->setExpectedException('InGeneral\Exception\MissingOptionException');
        $storage = new PhpArrayInFile(array());
        $storage->loadData();
    }


    public function testLoadDataWithNonExistentFile()
    {
        $this->setExpectedException('InAuthzWs\Client\Registry\Storage\Exception\LoadDataException');
        $storage = new PhpArrayInFile(array(
            'file' => '/some/non/existent/file'
        ));
        $storage->loadData();
    }


    public function testLoadDataWithBadStorageData()
    {
        $this->setExpectedException('InAuthzWs\Client\Registry\Storage\Exception\LoadDataException');
        $storage = new PhpArrayInFile(array(
            'file' => TESTS_DATA_DIR . '/clients/bad_storage.php'
        ));
        
        $storage->loadData();
    }


    public function testLoadData()
    {
        $storageFile = TESTS_DATA_DIR . '/clients/storage.php';
        $expectedData = require $storageFile;
        
        $storage = new PhpArrayInFile(array(
            'file' => $storageFile
        ));
        
        $this->assertSame($expectedData, $storage->loadData());
    }
}