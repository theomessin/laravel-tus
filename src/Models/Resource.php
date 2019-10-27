<?php

namespace Theomessin\Tus\Models;

use Theomessin\Tus\Models\Traits\HasCacheableMetadata;
use Theomessin\Tus\Models\Traits\HasMagicMetadata;

abstract class Resource
{
    use HasCacheableMetadata, HasMagicMetadata;

    /**
     *
     */
    public function __construct($key = null, $metadata = [])
    {
        $this->key = $key;
        $this->metadata = $metadata;
    }

    /**
     *
     */
    public static function create($key, $metadata)
    {
        $class = static::class;
        $object = new $class($key, $metadata);
        $object->save();
        return $object;
    }
}
