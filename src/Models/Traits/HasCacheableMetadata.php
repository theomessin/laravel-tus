<?php

namespace Theomessin\Tus\Models\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HasCacheableMetadata
{
    use HasMetadata;

    /**
     * @var string
     */
    protected $key;

    /**
     *
     */
    public static function find($key)
    {
        $metadata = Cache::get('tus-' . $key);
        if ($metadata == null) return null;

        $class = static::class;
        return new $class($key, $metadata);
    }

    /**
     *
     */
    public function save()
    {
        $this->enforceRequiredMetadata();
        $this->key = $this->key ?? Str::uuid()->toString();
        Cache::put('tus-' . $this->key, $this->metadata);
    }
}
