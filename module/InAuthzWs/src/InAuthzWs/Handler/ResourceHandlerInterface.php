<?php

namespace InAuthzWs\Handler;


interface ResourceHandlerInterface
{


    public function create(array $data, array $params = array());


    public function update($id, array $data, array $params = array());


    public function delete($id, array $params = array());


    public function fetch($id, array $params = array());


    public function fetchAll(array $params = array());
} 

