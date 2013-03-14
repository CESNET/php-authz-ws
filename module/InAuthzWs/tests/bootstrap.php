<?php
use InAuthzWs\Module;

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../Module.php';

define('TESTS_DATA_DIR', __DIR__ . '/data');

$module = new Module();
\Zend\Loader\AutoloaderFactory::factory($module->getAutoloaderConfig());

//----------
function _dump($value)
{
    error_log(print_r($value, true));
}