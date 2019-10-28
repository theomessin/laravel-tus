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

    public static function supportedHashAlgorithms()
    {
        $toLower = function ($a) {
            return  strtolower($a);
        };

        $algosArray = array_map($toLower, hash_algos());

        $doesNotContainComma = function ($a) {
            return strpos($a, ',') === false;
        };

        $algosArray = array_filter(hash_algos(), $doesNotContainComma);

        $hashAlgorithms = implode(',', $algosArray);

        return $hashAlgorithms;
    }

    public static function supportsHashAlgorithm($algorithm)
    {
        $toLCase = function ($a) {
            return strtolower($a);
        };

        $algosArray = array_map($toLCase, hash_algos());

        return in_array($algorithm, $algosArray);
    }

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

        $disk->append($file, $contents, '');
        $this->offset = $disk->size($file);
    }
}
