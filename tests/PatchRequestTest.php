<?php

namespace Theomessin\Tus\Tests;

use Theomessin\Tus\TusUploadResource;

class PatchRequestTest extends TestCase
{

    // override patch method to allow passing content too
    public function patch($uri, array $data = [], array $headers = [], $content = null)
    {
        return $this->call('PATCH', $uri, $data, [], [],  $this->transformHeadersToServerVars($headers), $content);
    }

    protected function sendPatchRequest($key, $offset, $payload)
    {
        $validRequestHeaders=[
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => $offset
        ];

        return $this->patch('/tus/' . $key, [], $validRequestHeaders, $payload);
    }

    /** @test */
    public function valid_offset_requests_are_applied_succesfully()
    {
        $this->withoutExceptionHandling();

        // initialize resource
        $resource = new TusUploadResource();
        $key=$resource->key;
        $offset=0;
        $chunkSize = 5;
        $payload = str_repeat("X", $chunkSize);

        foreach (range(0, 2) as $i) {

            $response=$this->sendPatchRequest($key, $offset, $payload);

            $response->assertStatus(204);

            $response->assertHeader('Upload-Offset', $offset + $chunkSize);

            $offset=$response->headers->get('Upload-Offset') ;
        }
    }

    /** @test */
    public function mismatched_offset_requests_return_409_conflict()
    {
        $key = "some-valid-key";

        // initialize resource
        $resource = new TusUploadResource($key);

        $response=$this->sendPatchRequest($key, 1, "payload");

        $response->assertStatus(409);
    }

    /** @test */
    public function invalid_content_type_requests_return_415_unsupported()
    {
        $invalidRequestHeaders=[
            'Content-Type' => 'not application/offset+octet-stream',
        ];

        $response= $this->patch('/tus/' . "any-key", [], $invalidRequestHeaders, "payload");

        $response->assertStatus(415);
    }

    /** @test */
    public function requests_against_non_existent_resources_return_404_not_found()
    {
        $response=$this->sendPatchRequest("not valid key", 0, "payload");

        $response->assertStatus(404);
    }
}
