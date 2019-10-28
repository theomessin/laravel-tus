<?php

namespace Theomessin\Tus\Tests;

use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Models\Upload;

class PatchTest extends TestCase
{
    private function patchUpload($uri, $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, [], $cookies, [], $server, $content);
    }

    /** @test */
    public function valid_offset_requests_are_applied_succesfully()
    {
        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => 44,
        ]);

        // Arrange: create a test file of 44 bytes.
        $contents = 'The quick brown fox jumps over the lazy dog.';

        // Arrange: the correct headers for the request.
        $headers = [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ];

        // Act: upload the file using a valid patch request.
        $response = $this->patchUpload('/tus/my-upload-key', $headers, $contents);

        // Assert: HTTP No Content.
        $response->assertStatus(204);

        // Assert: returned Upload-Offset is now the size of file.
        $response->assertHeader('Upload-Offset', 44);

        // Assert: the file was correctly saved under local/tus.
        $disk->assertExists('tus/my-upload-key');

        // Assert: the file contents is equal to contents uploaded.
        $this->assertEquals($contents, $disk->get('tus/my-upload-key'));
    }

    /** @test */
    public function mismatched_offset_requests_return_409_conflict()
    {
        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => 123,
        ]);

        // Arrange: invalid Upload-Offset headers for the request.
        $headers = [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 42,
        ];

        // Act: upload contents using patch with invalid headers.
        $response = $this->patch('/tus/my-upload-key', [], $headers);

        // Assert: HTTP Conflict.
        $response->assertStatus(409);
    }

    /** @test */
    public function invalid_content_type_requests_return_415_unsupported()
    {
        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => 123,
        ]);

        // Arrange: invalid Content-Type headers for the request.
        $headers = [
            'Content-Type' => 'some-random-content-type-header',
        ];

        // Act: upload contents using patch with invalid headers.
        $response = $this->patch('/tus/my-upload-key', [], $headers);

        // Assert: HTTP Unsupported.
        $response->assertStatus(415);
    }

    /** @test */
    public function requests_against_non_existent_resources_return_404_not_found()
    {
        // Act: patch request to non existent upload.
        $response = $this->patch('/tus/some-fake-key');

        // Assert: HTTP status code 404.
        $response->assertNotFound();
    }
}
