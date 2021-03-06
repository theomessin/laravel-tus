<?php

namespace Theomessin\Tus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Theomessin\Tus\Events\FileUploaded;
use Theomessin\Tus\Models\Upload;

class ProcessUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * @var Upload
     */
    public $upload;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param  Upload  $upload
     * @return void
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
        $this->disk = Storage::disk(config('tus.storage.disk'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $prefix = config('tus.storage.prefix');
        $stream = fopen($this->upload->accumulator, 'r');
        $this->disk->put("{$prefix}/{$this->upload->key}", $stream);
        unlink($this->upload->accumulator);

        // And now we're finished.
        event(new FileUploaded($this->upload));
    }
}
