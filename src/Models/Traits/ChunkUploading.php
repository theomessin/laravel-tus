<?php

namespace Theomessin\Tus\Models\Traits;

trait ChunkUploading
{
    /**
     * Auto-generates a temporary accumulator file path.
     * @todo only called if accumulator is passed.
     */
    public function setAccumulatorAttribute($value)
    {
        $this->attributes['accumulator'] = tempnam(null, "laravel-tus-{$this->key}-");
    }

    public function getOffsetAttribute()
    {
        clearstatcache();
        return filesize($this->accumulator);
    }

    public function appendToAccumulator($contents)
    {
        file_put_contents($this->accumulator, $contents, FILE_APPEND | LOCK_EX);
    }
}
