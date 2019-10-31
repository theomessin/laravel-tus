<?php

namespace Theomessin\Tus\Tests\Feature\Extensions;

use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Tests\Helpers\CanTusUpload;
use Theomessin\Tus\Tests\TestCase;

class IntegrationTest extends TestCase
{
    use CanTusUpload;

    /**
     * @var string
     */
    protected $contents;

    public function __construct()
    {
        parent::__construct();
        // Arrange: the awesome Lamb of God Omerta lyrics contents to be uploaded.
        $this->contents = 'Whoever appeals to the law against his fellow man is either a fool or a coward' . PHP_EOL;
        $this->contents .= 'Whoever cannot take care of himself without that law is both' . PHP_EOL;
        $this->contents .= 'For a wounded man will shall say to his assailant' . PHP_EOL;
        $this->contents .= '"If I live, I will kill you. If I die, you are forgiven"' . PHP_EOL;
        $this->contents .= 'Such is the rule of honor';
    }

    /** @test */
    public function a_full_chunked_upload_works()
    {
        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // Act: Upload the entire contents, with assertions.
        $key = $this->UploadViaTus($this->contents, 69, true);

        // Assert: the uploaded file is equal to the contents.
        $this->assertEquals($this->contents, $disk->get("tus/{$key}"));
    }

    /** @test */
    public function after_final_chunk_is_uploaded_key_is_deleted()
    {
        // Arrange: fake local disk.
        $disk = Storage::fake('local');

        // Act: Upload the entire contents as one chunk.
        $key = $this->UploadViaTus($this->contents, 0, false);

        // Assert: the upload resource is not found.
        $this->get("/tus/{$key}")->assertNotFound();
    }
}
