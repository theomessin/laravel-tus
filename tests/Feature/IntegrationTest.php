<?php

namespace Theomessin\Tus\Tests\Feature\Extensions;

use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Tests\TestCase;

class IntegrationTest extends TestCase
{
    /** @test */
    public function a_full_chunked_upload_works()
    {
        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // Arrange: the awesome Lamb of God Omerta lyrics contents to be uploaded.
        $contents = 'Whoever appeals to the law against his fellow man is either a fool or a coward' . PHP_EOL;
        $contents .= 'Whoever cannot take care of himself without that law is both' . PHP_EOL;
        $contents .= 'For a wounded man will shall say to his assailant' . PHP_EOL;
        $contents .= '"If I live, I will kill you. If I die, you are forgiven"' . PHP_EOL;
        $contents .= 'Such is the rule of honor';

        // Arrange: create the upload using the creation extension.
        $response = $this->post('/tus', [], ['Upload-Length' => strlen($contents)]);
        $location = $response->headers->get('Location');
        $key = basename($location);

        // Act: Upload the entire contents chunk by chunk.
        $this->ChunkedUpload($location, $contents, 5);

        // Assert: the uploaded file is equal to the contents.
        $this->assertEquals($contents, $disk->get("tus/{$key}"));
    }

    /**
     * Upload contents using tus, chunk by chunk
     *
     * @return  bool  Whether the complete upload was successful.
     */
    private function ChunkedUpload($location, $contents, $chunkSize, $offset = 0)
    {
        // If contents empty, we're done!
        if (strlen($contents) == 0) return;

        // Step 1: use HEAD to get current upload status.
        $response = $this->get($location);
        $response->assertHeader('Upload-Offset', $offset);
        $response->assertSuccessful();

        // Step 2: use PATCH to upload the next chunk.
        $chunk = substr($contents, 0, $chunkSize);
        $newOffset = $offset + strlen($chunk);
        $response = $this->TusPatchUpload($location, $chunk, $offset);
        $response->assertHeader('Upload-Offset', $newOffset);
        $response->assertStatus(204);

        // Step 3: Upload the rest of the contents.
        $remainingContents = substr($contents, $chunkSize);
        $this->ChunkedUpload($location, $remainingContents, $chunkSize, $newOffset);
    }

    /**
     * Fire a patch request with the valid tus protocol headers.
     */
    private function TusPatchUpload($location, $contents, $offset)
    {
        $cookies = $this->prepareCookiesForRequest();
        $server = $this->transformHeadersToServerVars([
            'Content-Type' => 'application/offset+octet-stream',
            'Upload-Offset' => $offset,
        ]);

        return $this->call('PATCH', $location, [], $cookies, [], $server, $contents);
    }
}
