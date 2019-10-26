<?php

namespace Theomessin\Tus\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;
use Theomessin\Tus\TusUploadResource;

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

    public function head($key)
    {
        $resource = TusUploadResource::get($key);

        if (!$resource) {
            return response(null, 404);
        }

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Upload-Offset' => $resource->offset,
            'Cache-Control' => 'no-store',
        ];

        if ($resource->length > 0) {
            $headers += ['Upload-Length' => $resource->length] ;
        }

        return response(null, 200)->withHeaders($headers);
    }
}
