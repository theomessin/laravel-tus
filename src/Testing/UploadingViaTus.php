<?php

namespace Theomessin\Tus\Testing;

use Theomessin\Tus\Models\Upload;

trait UploadingViaTus
{
    public function __construct()
    {
        parent::__construct();
        // Arrange: the awesome Lamb of God Omerta lyrics contents to be uploaded.
        $this->contents = 'Whoever appeals to the law against his fellow man is either a fool or a coward' . PHP_EOL;
        $this->contents .= 'Whoever cannot take care of himself without that law is both' . PHP_EOL;
        $this->contents .= 'For a wounded man will shall say to his assailant' . PHP_EOL;
        $this->contents .= '"If I live, I will kill you. If I die, you are forgiven"' . PHP_EOL;
        $this->contents .= 'Such is the rule of honor';
    }

    /**
     * Allows to upload some contents via Tus.
     *
     * @param  string  $contents  The contents to upload.
     * @param  int|null  $chunkSize  The size of each chunk.
     * @param  bool  $assertions  Whether to run assertions.
     * @return  Upload  The upload object.
     */
    protected function uploadViaTus($contents, $metadata = [], $chunkSize = null, $assertions = false)
    {
        // Arrange: prepare to encode metadata:
        $metadata = collect($metadata)->map(function ($v) {
            return base64_encode($v);
        })->map(function ($v, $k) {
             return "{$k} {$v}";
        })->flatten()->implode(',');

        // Arrange: prepare upload details.
        $uploadLength = strlen($contents);
        if ($chunkSize <= 0) $chunkSize = null;
        $chunkSize = $chunkSize ?? $uploadLength;

        // Arrange: prepare creation extension headers.
        $headers = ['Upload-Length' => $uploadLength];
        if ($metadata != '') {
            $headers += ['Upload-Metadata' => $metadata];
        }

        // Arrange: create the upload using the creation extension.
        $response = $this->post('/tus', [], $headers);
        $location = $response->headers->get('Location');

        // Act: Upload the entire contents chunk by chunk.
        $this->chunkedUpload($location, $contents, $chunkSize, $assertions);

        // Return the upload object.
        return Upload::withTrashed()->where('key', basename($location))->firstOrFail();
    }

    /**
     * Upload contents using tus, chunk by chunk
     *
     * @return  bool  Whether the complete upload was successful.
     */
    protected function chunkedUpload($location, $contents, $chunkSize, $assertions = false, $offset = 0)
    {
        // If contents empty, we're done!
        if (strlen($contents) == 0) return;

        // Step 1: use HEAD to get current upload status.
        $response = $this->get($location);

        // Step 1 assertions.
        if ($assertions) {
            $response->assertHeader('Upload-Offset', $offset);
            $response->assertSuccessful();
        }

        // Step 2: use PATCH to upload the next chunk.
        $chunk = substr($contents, 0, $chunkSize);
        $newOffset = $offset + strlen($chunk);
        $response = $this->tusPatch($location, $chunk, $offset);

        // Step 2 assertions.
        if ($assertions) {
            $response->assertHeader('Upload-Offset', $newOffset);
            $response->assertStatus(204);
        }

        // Step 3: Upload the rest of the contents.
        $remainingContents = substr($contents, $chunkSize);
        $this->chunkedUpload($location, $remainingContents, $chunkSize, $assertions, $newOffset);
    }

    /**
     * Fire a patch request with the valid tus protocol headers.
     */
    protected function tusPatch($location, $contents, $offset)
    {
        $cookies = $this->prepareCookiesForRequest();
        $server = $this->transformHeadersToServerVars([
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => $offset,
        ]);

        return $this->call('PATCH', $location, [], $cookies, [], $server, $contents);
    }
}
