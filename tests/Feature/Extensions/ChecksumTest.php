<?php

namespace Theomessin\Tus\Tests\Feature\Extensions;

use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class ChecksumTest extends TestCase
{
    private function patchUpload($uri, $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, [], $cookies, [], $server, $content);
    }


    /** @test */
    public function options_request_returns_checksum_extension()
    {
        // Arrange: Nothing to do

        // Act: send options request
        $response = $this->options('/tus');

        // Assert: check that response header Tus-Extension contains checksum
        $header  = $response->headers->get('Tus-Extension') ?? '';
        $this->assertStringContainsString('checksum', $header);
    }

    /** @test */
    public function options_request_returns_checksum_algorithm_header()
    {
         // Arrange: Nothing to do

        // Act: send options request
        $response = $this->options('/tus');

        // Assert: check that response header Tus-Extension contains checksum
        $header  = $response->headers->get('Tus-Checksum-Algorithm') ?? '';
        $this->assertStringContainsString('sha1', $header);
    }

    /** @test */
    public function patch_request_fails_400_if_checksum_algorithm_not_supported()
    {
        // Arrange: create a test chunk
        $content = 'The quick brown fox jumps over the lazy dog.';

        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => strlen($content),
        ]);

        // Arrange: prepare header
        $headers = [
            'Upload-Checksum' => 'not-supported-hash-algorithm 123456789',
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ];

        // Act: send PATCH request
        $response = $this->patchUpload('/tus/my-upload-key', $headers, $content);

         // Assert : status is 400
         $response->assertStatus(400);
    }

    /** @test */
    public function patch_request_fails_460_if_checksum_is_wrong()
    {
        // Arrange: create a test chunk
        $content = 'The quick brown fox jumps over the lazy dog.';

        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => strlen($content),
        ]);

        // Arrange: prepare header
        $headers = [
            'Upload-Checksum' => 'sha1 123456789',
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ];

        // Act: send PATCH request
        $response = $this->patchUpload('/tus/my-upload-key', $headers, $content);

         // Assert : status is 460
         $response->assertStatus(460);
    }

    /** @test */
    public function patch_request_succeeds_with_correct_checksum()
    {
        $this->withoutExceptionHandling();

        // Arrange: create a test chunk
        $content = 'The quick brown fox jumps over the lazy dog.';

        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => strlen($content),
        ]);

        // Arrange: prepare header
        $algorithm = 'sha1';
        $headers = [
            'Upload-Checksum' => $algorithm .' '. base64_encode(hash($algorithm, $content)),
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ];

        // Act: send PATCH request
        $response = $this->patchUpload('/tus/my-upload-key', $headers, $content);

         // Assert : status is 204
         $response->assertStatus(204);
    }
}
