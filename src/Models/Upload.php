<?php

namespace Theomessin\Tus\Models;

use Exception;
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
        // Helper variables for this ridiculously complex code.
        $disk = Storage::disk(config('tus.storage.disk'));
        $file = config('tus.storage.prefix') . '/' . $this->key;

        // We'll use this temporary file stream to "append".
        $ss = tmpfile();

        // If a file already exists, stream the contents into our temp stream.
        if ($disk->exists($file)) {
            $s_file = $disk->getDriver()->readStream($file);
            stream_copy_to_stream($s_file, $ss);
        }

        // Append new chunk contents to temp stream.
        fwrite($ss, $contents);

        // Stream temp stream back to disk.
        $disk->put($file, $ss);

        // Close the temporary stream.
        fclose($ss);

        // Update the upload offset with the new size on disk.
        $this->offset = $disk->size($file);
    }

    /**
     * Generates a url to the given Upload resource.
     */
    public function getUrl()
    {
        return route('tus.resource', ['upload' => $this->key]);
    }

    private function getMetadata()
    {
        if (! $this->has('metadata')) return collect();
        $encoded = $this->data['metadata'];

        try {
            $encoded = str_replace(', ', ',', $encoded);
            $metadata = collect(explode(',', $encoded));
            $metadata = $metadata->mapWithKeys(function ($v) {
                $parts = explode(' ', $v);
                return [$parts[0] => $parts[1]];
            });
            $metadata = $metadata->map(function ($v) {
                return base64_decode($v);
            });

            return $metadata;
        } catch (Exception $e) {
            return collect();
        }
    }

    /**
     * Extend magic data getter.
     */
    public function __get($name)
    {
        if ($name == 'metadata') {
            return $this->getMetadata();
        }

        if ($this->getMetadata()->has($name)) {
            return $this->getMetadata()[$name];
        }

        return parent::__get($name);
    }
}
