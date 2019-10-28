<?php

namespace Theomessin\Tus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Theomessin\Tus\Models\Upload;

class TusController extends Controller
{
    public function options()
    {
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
        ];

        return response(null, 204, $headers);
    }

    public function get(Upload $upload)
    {
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Upload-Offset' => $upload->offset,
            'Cache-Control' => 'no-store',
        ];

        if ($upload->length > 0) {
            $headers += ['Upload-Length' => $upload->length];
        }

        return response(null, 200, $headers);
    }

    public function patch(Request $request, Upload $upload)
    {
        $content = $request->getContent();
        $contentType = $request->header('Content-Type');
        $uploadOffset = $request->header('Upload-Offset');

        if ($contentType != 'application/offset+octet-stream') {
            return response(null, 415);
        }

        if ($upload->offset != $uploadOffset) {
            return response(null, 409);
        }

        $upload->append($content);

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Upload-Offset' => $upload->offset,
        ];

        return response(null, 204, $headers);
    }
}
