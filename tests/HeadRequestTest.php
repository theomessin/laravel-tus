<?php

namespace Theomessin\Tus\Tests;

use Theomessin\Tus\TusUploadResource;

class HeadRequestTest extends TestCase
{
    /** @test */
    public function valid_request_returns_offset_status_200()
    {
        $resource = new TusUploadResource();

        $response = $this->get('/tus/' . $resource->key);

        $response->assertHeader('Upload-Offset', 0);

        $resource->offset = 1000;

        $response = $this->get('/tus/' . $resource->key);

        $response->assertHeader('Upload-Offset', 1000);

        $response->assertStatus(200);
    }

    public function valid_request_returns_cache_control_header()
    {
        $resource = new TusUploadResource();

        $response = $this->get('/tus/' . $resource->key);

        $response->assertHeader('Cache-Control');

        $this->assertTrue(strpos( $response->headers->get('Cache-Control') , 'no-store') !== false );
    }

    /** @test */
    public function valid_request_returns_length_only_if_greater_0()
    {
        $resource = new TusUploadResource();

        $resource->length = 1000;

        $response = $this->get('/tus/' . $resource->key);

        $response->assertHeader('Upload-Length', 1000);

        $resource->length = 0;

        $response = $this->get('/tus/' . $resource->key);

        $response->assertHeaderMissing('Upload-Length');
    }

    /** @test */
    public function invalid_key_request_returns_404()
    {
        $response = $this->get('/tus/some-wrong-key');

        $response->assertStatus(404);
    }
}
