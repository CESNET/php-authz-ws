<?php

namespace InAuthzWs\Client\Registry\Storage;


interface StorageInterface
{


    /**
     * Loads client data from the storage.
     * 
     * @return array
     */
    public function loadData();
}