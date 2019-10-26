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

        return response(null, 204, $headers);
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
            $headers += ['Upload-Length' => $resource->length];
        }

        return response(null, 200, $headers);
    }

    public function patch($key)
    {
        $request = request();
        $contentType = $request->header('Content-Type');
        $uploadOffset = $request->header('Upload-Offset');
        $content = $request->getContent();

        if ($contentType != 'application/offset+octet-stream') {
            return response(null, 415);
        }

        $resource = TusUploadResource::get($key);

        // Even though this requirement is not defined in the protocol,
        // we will return 404 if there is no such resource, as is required for HEAD request
        if (!$resource) {
            return response(null, 404);
        }

        if ($resource->offset != $uploadOffset) {
            return response(null, 409);
        }

        $resource->append($content);

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Upload-Offset' => $resource->offset,
        ];

        return response(null, 204, $headers);
    }
}
