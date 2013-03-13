<?php

namespace InAuthzWs\Handler\Filter;


interface FilterFactoryInterface
{


    public function createDataFilter();


    public function createQueryFilter();
}