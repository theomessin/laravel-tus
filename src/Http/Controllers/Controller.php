<?php

namespace Theomessin\Tus\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;

class Controller extends LaravelController
{
    public function options()
    {
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
        ];

        return response(null, 204)->withHeaders($headers);
    }
}
