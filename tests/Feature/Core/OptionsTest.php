<?php

namespace Theomessin\Tus\Tests\Feature\Core;

use Theomessin\Tus\Tests\TestCase;

class OptionsTest extends TestCase
{
    /** @test */
    // @todo clean up and add testing A/A/A comments.
    public function respond_to_options_request_with_mandatory_headers()
    {
        $expectedHeaders = [
            'Tus-Resumable',
            'Tus-Version',
        ];

        $response = $this->options('/tus');

        $response->assertStatus(204);

        foreach ($expectedHeaders as $key => $header) {
            $response->assertHeader($header);
        }
    }
}
