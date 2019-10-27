<?php

namespace Theomessin\Tus\Models\Traits;

use Illuminate\Support\Collection;
use Theomessin\Tus\Exceptions\RequiredMetadataMissing;

trait HasMetadata
{
    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var string[]
     */
    protected $requiredMetadata = [];

    /**
     *
     */
    public function enforceRequiredMetadata()
    {
        $blueprint = new Collection($this->requiredMetadata);
        $metadata = new Collection($this->metadata);
        $diff = $blueprint->diff($metadata->keys());

        if ($diff->isNotEmpty()) {
            throw new RequiredMetadataMissing($diff);
        }
    }
}
