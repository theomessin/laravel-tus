<?php

namespace Theomessin\Tus\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\PatchTest;

class DoneTest extends PatchTest
{
    /** @test */
    public function after_completion_resource_is_deleted()
    {
        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // Arrange: create a test file of 44 bytes.
        $content = 'The quick brown fox jumps over the lazy dog.';

        // Arrange: create a test upload resource.
        $resource = Upload::create('my-upload-key', [
            'offset' => 0,
            'length' => strlen($content),
        ]);

        // Arrange: the correct headers for the request.
        $headers = [
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => 0,
        ];

        $resource = Upload::find('my-upload-key');

        $this->assertNotNull($resource);

        // Act: upload the file using a valid patch request.
        $response = $this->patchUpload('/tus/my-upload-key', $headers, $content);

        // Assert: HTTP No Content.
        $response->assertStatus(204);

        // Assert: returned Upload-Offset is now the size of file.
        $response->assertHeader('Upload-Offset', 44);

        // Assert: the file was correctly saved under local/tus.
        $disk->assertExists('tus/my-upload-key');

        // Assert: the file contents is equal to contents uploaded.
        $this->assertEquals($content, $disk->get('tus/my-upload-key'));

        $resource = Upload::find('my-upload-key');

        $this->assertNull($resource);
    }
}
