<?php

namespace Theomessin\Tus\Tests\Feature\Extensions;

use Theomessin\Tus\Tests\TestCase;

class CreationTest extends TestCase
{
    /** @test */
    public function options_request_returns_creation_extension()
    {
        // Act: send options request.
        $response = $this->options('/tus');

        // Assert: check that response header Tus-Extension contains creation.
        $header  = $response->headers->get('Tus-Extension') ?? '';
        $this->assertStringContainsString('creation', $header);
    }

    /** @test */
    public function valid_post_request_returns_expected_headers()
    {
        // Arrange: prepare request headers.
        $requestHeaders = [
            'Upload-Length' => 1000,
        ];

        // Act: send post request.
        $response = $this->post('/tus', [], $requestHeaders);

        // Assert: HTTP Created.
        $response->assertStatus(201);

        // Assert: returns location.
        $response->assertHeader('Location');
    }

    /** @test */
    public function post_request_metadata_returned_with_subsequent_head_request()
    {
        // Arrange: prepare request headers.
        $requestHeaders = [
            'Upload-Length' => 1000,
            'Upload-Metadata' => 'filename d29ybGRfZG9taW5hdGlvbl9wbGFuLnBkZg==',
        ];

        // Act: send post request, get location and send a head to that location.
        $response = $this->post('/tus', [], $requestHeaders);
        $response = $this->get($response->headers->get('Location'));

        // Assert: returns correct metadata.
        $response->assertHeader('Upload-Metadata', 'filename d29ybGRfZG9taW5hdGlvbl9wbGFuLnBkZg==');
    }

    /** @test */
    public function post_request_without_length_returns_400_without_location()
    {
           // Act: send post request without headers.
           $response = $this->post('/tus');

           // Assert: HTTP Bad Request.
           $response->assertStatus(400);

           // Assert: does not return location.
           $response->assertHeaderMissing('Location');
    }
}
