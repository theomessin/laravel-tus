<?php

namespace Theomessin\Tus\Models\Traits;

trait HasMagicMetadata
{
    use HasMetadata;

    /**
     * Magic metadata getter.
     */
    public function __get($name)
    {
        return $this->metadata[$name];
    }

    /**
     * Magic metadata setter.
     */
    public function __set($name, $value)
    {
        $this->metadata[$name] = $value;
    }
}
