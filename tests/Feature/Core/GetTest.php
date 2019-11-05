<?php

namespace Theomessin\Tus\Tests\Feature\Core;

use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class GetTest extends TestCase
{
    /** @test */
    public function valid_request_returns_with_correct_headers()
    {
        // Arrange: create test Upload.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
            'length' => 123,
        ]);

        // Act: get request for test Upload.
        $response = $this->get('/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325');

        // Assert: response code is 200.
        $response->assertSuccessful();

        // Assert: the Upload-Offset header is correct.
        // Note: default offset is zero since there are no chunks.
        $response->assertHeader('Upload-Offset', 0);

        // Assert: the Upload-Length header is correct.
        $response->assertHeader('Upload-Length', 123);
    }

    /** @test */
    public function valid_request_returns_cache_control_header()
    {
        // Arrange: create test Upload.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
        ]);

        // Act: get request for test Upload.
        $response = $this->get('/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325');

        // Assert: response code is 200.
        $response->assertSuccessful();

        // Assert: Cache-Control header returns no-store
        $CacheControlHeader  = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $CacheControlHeader);
    }

    /** @test */
    public function upload_length_header_is_missing_for_length_0()
    {
        // Arrange: create test Upload.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
            'length' => 0,
        ]);

        // Act: get request for test Upload.
        $response = $this->get('/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325');

        // Assert: response code is 200.
        $response->assertSuccessful();

        // Assert: the Upload-Length header is missing.
        $response->assertHeaderMissing('Upload-Length');
    }

    /** @test */
    public function invalid_upload_resource_key_responds_with_404()
    {
        // Act: get request for a non existant Upload.
        $response = $this->get('/tus/some-non-existant-uuid-key');

        // Assert: response code is 404.
        $response->assertNotFound();
    }
}
