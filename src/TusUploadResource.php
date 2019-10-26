<?php

namespace Theomessin\Tus;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TusUploadResource
{
    const PREFIX = ':tus:';
    public $key = 0;
    public $offset = 0;
    public $length = 0;

    public function __construct($key = null, $length = 0)
    {
        $this->key = $key ?? Str::uuid()->toString();
        $this->length = $length;
        Cache::add(self::PREFIX . $this->key, $this);
    }

    public static function get($key)
    {
        return Cache::get(self::PREFIX . $key, null);
    }
}
