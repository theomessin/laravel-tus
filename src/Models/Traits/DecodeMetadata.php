<?php

namespace Theomessin\Tus\Models\Traits;

use Illuminate\Support\Collection;

trait DecodeMetadata
{
    public function getMetadataAttribute($value)
    {
        if ($value == null) return new Collection;

        try {
            $encoded = str_replace(', ', ',', $value);
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
            //
        }
    }
}
