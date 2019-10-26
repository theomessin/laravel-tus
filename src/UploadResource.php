<?php

namespace Theomessin\Tus;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UploadResource
{
    const PREFIX = ':tus:';
    public $key = 0;
    public $offset = 0;
    public $length;
    public $fileName;

    public function __construct($key = null, $length = 0)
    {
        $this->key = $key ?? Str::uuid();
        $this->length = $length;
        $this->fileName = $key;
        Cache::add(self::PREFIX . $key, $this);
    }

    public static function get($key)
    {
        return Cache::get(self::PREFIX . $key, null);
    }

    public function append($chunk)
    {
        // THIS NEEDS TO BE REVISITED FOR PERFORMANCE
        // append actualy retrieves and rewrites the full file contents !!!
        // last parameter is to overide the default separator of PHP_EOL (undocumented feature !)
        Storage::append($this->fileName, $chunk, '');
        $this->offset = Storage::size($this->fileName);
    }
}
