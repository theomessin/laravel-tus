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
        $data = Cache::get('tus-' . $key);
        if ($data == null) return null;

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
    }

    /**
     * Check is attribute is defined
     */
    public function has($name)
    {
        return isset($this->data[$name]);
    }
}
