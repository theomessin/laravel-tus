<?php

namespace Theomessin\Tus\Tests\Feature\CoreProtocol;

use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class HttpGetTest extends TestCase
{
    /** @test */
    public function valid_request_returns_with_correct_headers()
    {
        $this->withoutExceptionHandling();
        // Arrange: create test Upload.
        $resource = Upload::create('my-upload-key', [
            'offset' => 321,
            'length' => 123,
        ]);

        // Act: get request for test Upload.
        $response = $this->get('/tus/my-upload-key');

        // Assert: response code is 200.
        $response->assertSuccessful();

        // Assert: the Upload-Offset header is correct.
        $response->assertHeader('Upload-Offset', 321);

        // Assert: the Upload-Length header is correct.
        $response->assertHeader('Upload-Length', 123);
    }

    /** @test */
    // @todo clean up and add testing A/A/A comments.
    public function valid_request_returns_cache_control_header()
    {
        $resource = Upload::create('my-upload-key', [
            'offset' => 123,
            'length' => 321,
        ]);

        $response = $this->get('/tus/my-upload-key');

        // Assert: response code is 200.
        $response->assertSuccessful();

        $response->assertHeader('Cache-Control');

        // @PMessinezis the following line makes no sense.. Please explain.
        $this->assertTrue(strpos($response->headers->get('Cache-Control'), 'no-store') !== false);
    }

    /** @test */
    public function upload_length_header_is_missing_for_length_0()
    {
        // Arrange: create test Upload.
        $resource = Upload::create('my-upload-key', [
            'offset' => 911,
            'length' => 0,
        ]);

        // Act: get request for test Upload.
        $response = $this->get('/tus/my-upload-key');

        // Assert: response code is 200.
        $response->assertSuccessful();

        // Assert: the Upload-Length header is missing.
        $response->assertHeaderMissing('Upload-Length');
    }

    /** @test */
    public function invalid_upload_resource_key_responds_with_404()
    {
        // Act: get request for a non existant Upload.
        $response = $this->get('/tus/some-non-existant-key');

        // Assert: response code is 404.
        $response->assertNotFound();
    }
}
