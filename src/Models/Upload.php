<?php

namespace Theomessin\Tus\Models;

class Upload extends Resource
{
    /**
     * @var string[]
     */
    protected $requiredData = [
        'offset',
        'length',
    ];
}
