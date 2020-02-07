<?php

namespace Theomessin\Tus\Testing;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Theomessin\Tus\Models\Upload;

/**
 * @codeCoverageIgnore
 */
trait TusUploading
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
     * Allows to upload contents via Tus.
     *
     * @param  string|resource  $source  The source to upload.
     * @param  int|null  $chunkSize  The size of each chunk.
     * @param  bool  $assertions  Whether to run assertions.
     * @return  Upload  The upload object.
     */
    public function tusUpload($source, $metadata = [], $chunkSize = null, $assertions = false)
    {
        $method = (new Collection([
            'resource' => 'tusUploadStream',
            'string' => 'tusUploadString',
        ]))->get(gettype($source));

        if ($method === null) {
            throw new InvalidArgumentException('Argument source is of invalid type');
        }

        return $this->$method($source, $metadata, $chunkSize, $assertions);
    }

    private function tusUploadString($string, $metadata, $chunkSize, $assertions)
    {
        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, $string);
        rewind($stream);
        return $this->tusUploadStream($stream, $metadata, $chunkSize, $assertions);
    }

    private function tusUploadStream($stream, $metadata, $chunkSize, $assertions)
    {
        // Arrange: prepare to encode metadata:
        $metadata = collect($metadata)->map(function ($v) {
            return base64_encode($v);
        })->map(function ($v, $k) {
             return "{$k} {$v}";
        })->flatten()->implode(',');

        // Arrange: prepare upload details.
        $uploadLength = fstat($stream)['size'];
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
        $response->assertSuccessful();

        // Act: Upload the entire stream chunk by chunk.
        $this->chunkedUpload($location, $stream, $chunkSize, $assertions);

        // Return the upload object.
        return Upload::withTrashed()->where('key', basename($location))->firstOrFail();
    }

    /**
     * Upload stream using tus, chunk by chunk
     *
     * @return  bool  Whether the complete upload was successful.
     */
    private function chunkedUpload($location, $stream, $chunkSize, $assertions = false, $offset = 0)
    {
        // If stream empty, we're done!
        if (feof($stream) == true) return;

        // Step 1: use HEAD to get current upload status.
        $response = $this->get($location);

        // Step 1 assertions.
        if ($assertions) {
            $response->assertHeader('Upload-Offset', $offset);
            $response->assertSuccessful();
        }

        // Step 2: use PATCH to upload the next chunk.
        $chunk = fread($stream, $chunkSize);
        $newOffset = $offset + strlen($chunk);
        $response = $this->tusPatch($location, $chunk, $offset);

        // Step 2 assertions.
        if ($assertions) {
            $response->assertHeader('Upload-Offset', $newOffset);
            $response->assertStatus(204);
        }

        // Step 3: Upload the rest of the contents.
        $this->chunkedUpload($location, $stream, $chunkSize, $assertions, $newOffset);
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
