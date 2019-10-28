<?php

namespace Theomessin\Tus\Tests\Feature\Extensions;

use Theomessin\Tus\Tests\TestCase;

class CreationTest extends TestCase
{
    /** @test */
    public function options_request_returns_creation_extension()
    {
        // Arrange: Nothing to do

        // Act: send options request
        $response = $this->options('/tus');

        // Assert: check that response header Tus-Extension contains creation
        $header  = $response->headers->get('Tus-Extension') ?? '';
        $this->assertStringContainsString('creation', $header);
    }

    /** @test */
    public function valid_post_request_returns_expected_headers()
    {

        $this->withoutExceptionHandling();

        // Arrange: prepare request headers
        $requestHeaders = [
            'Upload-Length' => 1000,
        ];

        // Act: send post request
        $response = $this->post('/tus', [], $requestHeaders);

        //Assert: returns status 201
        $response->assertStatus(201);

        // Assert: returns location
        $response->assertHeader('Location');
    }

    /** @test */
    public function post_request_metadata_returned_with_subsequent_head_request()
    {
        $this->withoutExceptionHandling();

        // Arrange: prepare request headers
        $requestHeaders = [
            'Upload-Length' => 1000,
            'Upload-Metadata' => 'some-metadata',
        ];

        // Act: send post request, get location and send a head to that location
        $response = $this->post('/tus', [], $requestHeaders);
        $location = $response->headers->get('Location');
        $response = $this->get($location);

        //Assert: returns metadata
        $response->assertHeader('Upload-Metadata', 'some-metadata');
    }

    /** @test */
    public function post_request_without_length_returns_400_without_location()
    {

           // Act: send post request without headers
           $response = $this->post('/tus');

           //Assert : returns 400
           $response->assertStatus(400);

           //Assert : does not return location
           $response->assertHeaderMissing('Location');
    }
}
