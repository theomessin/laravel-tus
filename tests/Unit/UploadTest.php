<?php

namespace Theomessin\Tus\Tests\Unit;

use Illuminate\Support\Collection;
use Theomessin\Tus\Models\Upload;
use Theomessin\Tus\Tests\TestCase;

class UploadTest extends TestCase
{
    /** @test */
    public function metadata_magic_accessor_decodes_metadata_from_data()
    {
        // Arrange: some testing metadata.
        $encodedMetadata = 'filename d29ybGRfZG9taW5hdGlvbl9wbGFuLnBkZg==, mimetype dGV4dC9wbGFpbg==';

        // Arrange: create a new Upload.
        $uut = new Upload('some-key', [
            'metadata' => $encodedMetadata,
        ]);

        // Act: use the magic metadata accessor.
        $metadata = $uut->metadata;

        // Assert: the metadata "attribute" is a Collection.
        $this->assertInstanceOf(Collection::class, $metadata);

        // Assert: the metadata is decoded correctly.
        $this->assertEquals('text/plain', $metadata['mimetype']);

        // Assert: multiple metadata values are decoded correctly.
        $this->assertEquals('world_domination_plan.pdf', $metadata['filename']);
    }

    /** @test */
    public function metadata_magic_accessor_works_without_metadata_too()
    {
        // Arrange: create a new Upload.
        $uut = new Upload('some-key');

        // Act: use the magic metadata accessor.
        $metadata = $uut->metadata;

        // Assert: the metadata "attribute" is a Collection.
        $this->assertInstanceOf(Collection::class, $metadata);

        // Assert: the metadata "attribute" is empty.
        $this->assertEquals(0, $metadata->count());
    }
}
