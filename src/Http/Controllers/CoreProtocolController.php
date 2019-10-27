<?php

namespace Theomessin\Tus\Http\Controllers;

use Illuminate\Routing\Controller;
use Theomessin\Tus\Models\Upload;

class CoreProtocolController extends Controller
{
    public function options()
    {
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
        ];

        return response(null, 204)->withHeaders($headers);
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

        return response(null, 200)->withHeaders($headers);
    }
}
