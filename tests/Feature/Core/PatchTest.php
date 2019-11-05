<?php

namespace Theomessin\Tus\Tests\Core;

use Illuminate\Support\Facades\File;
use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class PatchTest extends TestCase
{
    protected function patchUpload($uri, $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, [], $cookies, [], $server, $content);
    }

    /** @test */
    public function valid_offset_concecutive_requests_are_applied_succesfully()
    {
        // Arrange: create a test upload resource.
        // Note: default offset is zero since there are no chunks.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
        ]);

        // Arrange: this is where to fire patch requests.
        $uri = '/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325';

        // Arrange: create a test file of 35 + 35 bytes.
        $contents_1 = 'This is the first line of contents' . PHP_EOL;
        $contents_2 = 'This is the second line of contents';

        // Act: upload the contents$ using a valid patch request.
        $response = $this->patchUpload($uri, [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ], $contents_1);

        // Assert: HTTP No Content.
        $response->assertStatus(204);

        // Assert: returned Upload-Offset is now the size of what we sent.
        $response->assertHeader('Upload-Offset', 35);

        // Act: upload the contents$ using a valid patch request.
        $response = $this->patchUpload($uri, [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 35,
        ], $contents_2);

        // Assert: HTTP No Content.
        $response->assertStatus(204);

        // Assert: returned Upload-Offset is now the size of what we sent.
        $response->assertHeader('Upload-Offset', 70);

        // Note: this is where the chunks should be accumulated.
        $accumulator = Upload::firstOrFail()->accumulator;

        // Assert: the accumulator exists.
        $this->assertTrue(File::exists($accumulator));

        // Assert: the file contents are equal to the contents uploaded.
        $this->assertEquals($contents_1 . $contents_2, file_get_contents($accumulator));
    }

    /** @test */
    public function mismatched_offset_requests_return_409_conflict()
    {
        // Arrange: create a test upload resource.
        // Note: default offset is zero since there are no chunks.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
        ]);

        // Arrange: invalid Upload-Offset headers for the request.
        $headers = [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 42,
        ];

        // Act: upload contents using patch with invalid headers.
        $response = $this->patch('/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325', [], $headers);

        // Assert: HTTP Conflict.
        $response->assertStatus(409);
    }

    /** @test */
    public function invalid_content_type_requests_return_415_unsupported()
    {
        // Arrange: create a test upload resource.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
        ]);

        // Arrange: invalid Content-Type headers for the request.
        $headers = [
            'Content-Type' => 'some-random-content-type-header',
        ];

        // Act: upload contents using patch with invalid headers.
        $response = $this->patch('/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325', [], $headers);

        // Assert: HTTP Unsupported.
        $response->assertStatus(415);
    }

    /** @test */
    public function requests_against_non_existent_resources_return_404_not_found()
    {
        // Act: patch request to non existent upload.
        $response = $this->patch('/tus/some-non-existant-uuid-key');

        // Assert: HTTP status code 404.
        $response->assertNotFound();
    }
}
