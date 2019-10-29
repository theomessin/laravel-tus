<?php

namespace Theomessin\Tus\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Theomessin\Tus\Exceptions\RequiredDataMissing;

trait HasCacheableData
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string[]
     */
    public $requiredData = [];

    /**
     *
     */
    public static function find($key)
    {
        if (! Cache::has("tus-{$key}")) {
            return null;
        }

        $data = Cache::get("tus-{$key}");
        $class = static::class;
        return new $class($key, $data);
    }

    /**
     *
     */
    public function save()
    {
        $this->enforceRequiredData();
        $this->key = $this->key ?? Str::uuid()->toString();
        Cache::put('tus-' . $this->key, $this->data);
    }

    /**
     *
     */
    public function enforceRequiredData()
    {
        $blueprint = new Collection($this->requiredData);
        $data = new Collection($this->data);
        $diff = $blueprint->diff($data->keys());

        if ($diff->isNotEmpty()) {
            throw new RequiredDataMissing($diff);
        }
    }

    /**
     * Magic data getter.
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * Magic data setter.
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        $this->save();
    }

    /**
     * Check if attribute is defined and not null
     */
    public function has($name)
    {
        return isset($this->data[$name]);
    }
}
