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
            'Tus-Extension' => 'creation',
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

        if ($upload->has('metadata')) {
            $headers += ['Upload-Metadata' => $upload->metadata];
        }

        return response(null, 200)->withHeaders($headers);
    }

    public function post(Request $request)
    {
        $length = intval($request->header('Upload-Length'));
        $metadata = $request->header('Upload-Metadata');

        if (! $length) {
            return abort(400);
        }


        if ($length < 0) {
            return response(400);
        }

        $upload = Upload::create(null, [
            'offset' => 0,
            'length' => $length,
            'metadata' => $metadata,
        ]);

        $location = '/tus/' . $upload->key;
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Location' => $location,
        ];

        return response(null, 201, $headers);
    }
}
