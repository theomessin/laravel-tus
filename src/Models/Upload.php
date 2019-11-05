<?php

namespace Theomessin\Tus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Theomessin\Tus\Models\Traits\ChunkUploading;
use Theomessin\Tus\Models\Traits\DecodeMetadata;

class Upload extends Model
{
    use ChunkUploading, DecodeMetadata, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Auto-generates a UUID key if $value is extra special.
     * @todo only called if a key is passed.
     */
    public function setKeyAttribute($value)
    {
        if ($value == 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx') {
            $value = Str::uuid()->toString();
        }

        $this->attributes['key'] = $value;
    }
}
