<?php

namespace Theomessin\Tus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Theomessin\Tus\Jobs\ProcessUpload;
use Theomessin\Tus\Models\Upload;

class TusController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function options()
    {
        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Tus-Extension' => 'creation',
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

        if ($upload->getAttributes()['metadata'] !== null) {
            $headers += ['Upload-Metadata' => $upload->getAttributes()['metadata']];
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

        $upload->appendToAccumulator($content);

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Tus-Version' => '1.0.0',
            'Upload-Offset' => $upload->offset,
        ];

        if ($upload->offset == $upload->length) {
            $upload->delete();  // Soft delete.
            ProcessUpload::dispatch($upload);
        }

        return response(null, 204, $headers);
    }

    public function post(Request $request)
    {
        $length = intval($request->header('Upload-Length'));
        $metadata = $request->header('Upload-Metadata');

        if ($length < 1) {
            return abort(400);
        }

        $upload = Upload::create([
            'length' => $length,
            'metadata' => $metadata,
            'user_id' => Auth::user()->id,
            // @todo I hate that I need these here:
            'key' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            'accumulator' => 'this-will-magically-change',
        ]);

        $location = route('tus.resource', ['upload' => $upload->key]);

        $headers = [
            'Tus-Resumable' => '1.0.0',
            'Location' => $location,
        ];

        return response(null, 201, $headers);
    }
}
