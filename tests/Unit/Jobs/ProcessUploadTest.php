<?php

namespace Theomessin\Tus\Tests\Unit\Jobs;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Jobs\ProcessUpload;
use Theomessin\Tus\Testing\UploadingViaTus;
use Theomessin\Tus\Tests\TestCase;

class ProcessUploadTest extends TestCase
{
    use UploadingViaTus;

    /** @test */
    public function job_handle_streams_completed_upload_from_accumulator_to_correct_disk_location()
    {
        // Arrange: fake queue.
        Queue::fake();

        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // (Arrange) Act: Upload the entire contents, with assertions.
        $upload = $this->uploadViaTus($this->contents, [], 0, false);

        // (Arrange) Assert: the uploaded file is equal to the contents.
        $this->assertEquals($this->contents, file_get_contents($upload->accumulator));

        // (Arrange) Assert: the complete file was uploaded so the ProcessUpload job was pushed.
        Queue::assertPushed(ProcessUpload::class, function ($job) use ($upload) {
            return $job->upload->id === $upload->id;
        });

        // Act: create the unit under test and call handle.
        $uut = (new ProcessUpload($upload))->handle();

        // Assert: the file was copied successfuly.
        $this->assertEquals($this->contents, $disk->get("tus/{$upload->key}"));

        // Assert: The temporary file has been deleted.
        $this->assertFalse(file_exists($upload->accumulator));
    }
}
