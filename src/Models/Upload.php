<?php

namespace Theomessin\Tus\Models;

class Upload extends Resource
{
    /**
     * @var string[]
     */
    protected $requiredMetadata = [
        'offset',
        'length',
    ];
}
