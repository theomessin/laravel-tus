<?php

namespace Theomessin\Tus\Models;

use Theomessin\Tus\Models\Traits\HasCacheableData;

abstract class Resource
{
    use HasCacheableData;

    /**
     *
     */
    public function __construct($key = null, $data = [])
    {
        $this->key = $key;
        $this->data = $data;
    }

    /**
     *
     */
    public static function create($key, $data)
    {
        $class = static::class;
        $object = new $class($key, $data);
        $object->save();
        return $object;
    }
}
