<?php

namespace Theomessin\Tus\Tests\Helpers;

trait CanTusUpload
{
    /**
     * Allows to upload some contents via Tus.
     *
     * @param  string  $contents  The contents to upload.
     * @param  int|null  $chunkSize  The size of each chunk.
     * @param  bool  $assertions  Whether to run assertions.
     * @return  string  The key of the upload.
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
        $this->ChunkedUpload($location, $contents, $chunkSize, $assertions);

        // Return the upload key.
        return basename($location);
    }

    /**
     * Upload contents using tus, chunk by chunk
     *
     * @return  bool  Whether the complete upload was successful.
     */
    protected function ChunkedUpload($location, $contents, $chunkSize, $assertions = false, $offset = 0)
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
        $this->ChunkedUpload($location, $remainingContents, $chunkSize, $assertions, $newOffset);
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
