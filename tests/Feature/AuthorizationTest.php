<?php

namespace Theomessin\Tus\Tests\Core;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\File;
use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class AuthorizationTest extends TestCase
{
    protected function patchUpload($uri, $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, [], $cookies, [], $server, $content);
    }

    /** @test */
    public function only_the_user_that_created_an_upload_can_access_it()
    {
        // Arrange: hijacker user with id of 2.
        $hijacker = factory(User::class)->create();

        // Arrange: create a test upload resource.
        // Note: default offset is zero since there are no chunks.
        factory(Upload::class)->create([
            'key' => 'b4fbee15-16ac-44ec-aed6-c1c5a9c10325',
        ]);

        // Arrange: this is where to fire patch requests.
        $uri = '/tus/b4fbee15-16ac-44ec-aed6-c1c5a9c10325';

        // Arrange: create a test file of 35 + (35 hijack) bytes.
        $contents_1 = 'This is the first line of contents' . PHP_EOL;
        $contents_2 = 'I just tried to hijack this upload.';

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
        $response = $this->actingAs($hijacker)->patchUpload($uri, [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 35,
        ], $contents_2);

        // Assert: HTTP No Content.
        $response->assertForbidden();

        // Note: this is where the chunks should be accumulated.
        $accumulator = Upload::firstOrFail()->accumulator;

        // Assert: the accumulator exists.
        $this->assertTrue(File::exists($accumulator));

        // Assert: the file contents are equal to the contents uploaded.
        $this->assertEquals($contents_1, file_get_contents($accumulator));
    }
}
