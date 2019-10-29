<?php

namespace Theomessin\Tus\Tests\Feature;

use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class MetadataTest extends TestCase
{
    /** @test */
    public function retrieve_metadata_as_an_assoc_array()
    {
        //Arrange: Create a metadata structure and encode it

        $metadata = [
            'key_one' => 'value_one',
            'key_two' => 'value_two',
            'key_for_space' => ' ',
            'key_zero' => '0',
            'keyWithoutValue' => null,
        ];

        $encodedMetadata = '';
        foreach ($metadata as $key => $value) {
            $encodedMetadata .= $key . ' ' . base64_encode($value). ',';
        }

        //trim trailing comma
        $encodedMetadata = trim($encodedMetadata, ',');
        //trim trailing space
        $encodedMetadata = trim($encodedMetadata);

        //Arrange: prepare post request headers
        $requestHeaders = [
            'Upload-Length' => 1000,
            'Upload-Metadata' => $encodedMetadata,
        ];

        // Act: send post request
        $response = $this->post('/tus', [], $requestHeaders);

        //Act: get location from responce and get key from it and Upload
        $location = $response->headers->get('Location');
        $key = basename($location);
        $upload = Upload::find($key);

        //Assert: resource has encoded metadata
        $this->assertSame($encodedMetadata, $upload->encodedMetadata);

        //Assert: resource returns metadata decoded
        $this->assertSame($metadata, $upload->metadata);
    }
}
