<?php
use InAuthzWs\Module;

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../Module.php';

$module = new Module();
\Zend\Loader\AutoloaderFactory::factory($module->getAutoloaderConfig());