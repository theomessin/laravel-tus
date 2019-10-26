<?php

namespace Theomessin\Tus\Tests;

use Illuminate\Support\Facades\Artisan;

class OptionsRequestTest extends TestCase
{
    /** @test */
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
