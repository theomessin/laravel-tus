<?php

namespace Theomessin\Tus\Models;

use Theomessin\Tus\Models\Traits\HasCacheableMetadata;
use Theomessin\Tus\Models\Traits\HasMagicMetadata;
use Theomessin\Tus\Models\Traits\HasMetadata;

abstract class Resource
{
    use HasMetadata, HasMagicMetadata, HasCacheableMetadata;

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
