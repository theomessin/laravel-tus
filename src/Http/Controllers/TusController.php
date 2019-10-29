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
            'Tus-Extension' => 'creation,checksum',
            'Tus-Checksum-Algorithm' => Upload::supportedHashAlgorithms(),
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

        if ($upload->has('metadata')) {
            $headers += ['Upload-Metadata' => $upload->metadata];
        }

        return response(null, 200, $headers);
    }

    public function patch(Request $request, Upload $upload)
    {
        $content = $request->getContent();
        $contentType = $request->header('Content-Type');
        $uploadOffset = $request->header('Upload-Offset');
        $checksumHeader = $request->header('Upload-Checksum');

        if ($contentType != 'application/offset+octet-stream') {
            return response(null, 415);
        }

        if ($checksumHeader) {
            //header expected in format "algorithm checksum"
            [$algorithm, $checksumSent] = explode(' ', $checksumHeader);

            if (! Upload::supportsHashAlgorithm($algorithm)) {
                return response(null, 400);
            }

            $contentChecksum = hash($algorithm, $content);

            //checksum expected base64_encoded
            $checksumSent = base64_decode($checksumSent);

            if ($contentChecksum != $checksumSent) {
                return response(null, 460);
            }
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

        if ($upload->offset == $upload->length) {
            $this->done($upload);
            $upload->delete();
        }

        return response(null, 204, $headers);
    }

    public function post(Request $request)
    {
        $length = $request->header('Upload-Length');
        $metadata = $request->header('Upload-Metadata');

        if (!$length) {
            return abort(400);
        }

        $length = intval($length);

        if ($length < 1) {
            return abort(400);
        }

        $upload = Upload::create(null, [
            'offset' => 0,
            'length' => $length,
            'metadata' => $metadata,
        ]);

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Location' => $upload->getUrl(),
        ];

        return response(null, 201, $headers);
    }

    public function done(Upload $upload)
    {
        # code... to be overriden
    }
}
