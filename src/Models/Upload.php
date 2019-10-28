<?php

namespace Theomessin\Tus\Models;

use Illuminate\Support\Facades\Storage;

class Upload extends Resource
{
    /**
     * @var string[]
     */
    public $requiredData = [
        'offset',
        'length',
    ];

    /**
     *
     */
    protected function getFileName()
    {
        return storage_path("tus/{$this->key}");
    }

    /**
     * @todo refactor File stuff to seperate classes/traits.
     */
    public function append($contents)
    {
        $file = config('tus.storage.prefix') . '/' . $this->key;
        $disk = Storage::disk(config('tus.storage.disk'));

        $disk->append($file, $contents);
        $this->offset = $disk->size($file);
    }
}
