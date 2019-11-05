<?php

namespace Theomessin\Tus\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Theomessin\Tus\Jobs\ProcessUpload;
use Theomessin\Tus\Testing\UploadingViaTus;
use Theomessin\Tus\Tests\TestCase;

class IntegrationTest extends TestCase
{
    use UploadingViaTus;

    /**
     * @var string
     */
    protected $contents;

    /** @test */
    public function a_full_chunked_upload_works()
    {
        // Arrange: fake queue.
        Queue::fake();

        // Act: Upload the entire contents, with assertions.
        $upload = $this->uploadViaTus($this->contents, [], 69, true);

        // Assert: the uploaded file is equal to the contents.
        $this->assertEquals($this->contents, file_get_contents($upload->accumulator));

        // Assert: the complete file was uploaded so the ProcessUpload job was pushed.
        Queue::assertPushed(ProcessUpload::class, function ($job) use ($upload) {
            return $job->upload->id === $upload->id;
        });
    }

    /** @test */
    public function after_final_chunk_is_uploaded_key_is_deleted()
    {
        // Act: Upload the entire contents as one chunk.
        $upload = $this->uploadViaTus($this->contents, [], 0, false);

        // Assert: the upload resource is not found.
        $this->get("/tus/{$upload->key}")->assertNotFound();
    }
}
