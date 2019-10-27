<?php

namespace Theomessin\Tus\Models;

use Theomessin\Tus\Models\Traits\HasCacheableMetadata;
use Theomessin\Tus\Models\Traits\HasMagicMetadata;

abstract class Resource
{
    use HasMagicMetadata, HasCacheableMetadata {
        // This here is needed to fix the trait collision in php-7.2.
        // @todo probably fix by refactoring the traits structure instead.
        HasCacheableMetadata::enforceRequiredMetadata insteadof HasMagicMetadata;
    }

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
