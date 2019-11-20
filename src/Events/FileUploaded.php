<?php

namespace Theomessin\Tus\Events;

use Illuminate\Queue\SerializesModels;
use Theomessin\Tus\Models\Upload;

class FileUploaded
{
    use SerializesModels;

    public $upload;

    /**
     * Create a new event instance.
     *
     * @param  Upload  $upload
     * @return void
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }
}
