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
    public $fileName;

    public function __construct($key = null, $length = 0)
    {
        $this->key = $key ?? Str::uuid()->toString();
        $this->length = $length;

        //PRODUCTION VERSION
        $this->fileName = config('tus.upload_path') . $this->key;

        // TEST VERSION - need to find out how to have one version because
        // using storage_path() inside config.php points to vendor/orchestra/... during test
        $this->fileName = storage_path(config('tus.upload_path')) . $this->key;

        Cache::add(self::PREFIX . $this->key, $this);
    }

    public static function get($key)
    {
        return Cache::get(self::PREFIX . $key, null);
    }

    public function append($chunk)
    {
        file_put_contents($this->fileName, $chunk, FILE_APPEND);
        clearstatcache();
        $this->offset = filesize($this->fileName);
    }
}
