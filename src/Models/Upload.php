<?php

namespace Theomessin\Tus\Models;

class Upload extends Resource
{
    /**
     * @var string[]
     */
    public $requiredData = [
        'offset',
        'length',
    ];
}
