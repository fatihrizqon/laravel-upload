<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ImageConverterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($temporaryFile)
    {
        $this->temporaryFile = $temporaryFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Storage::move("uploads/tmp/{$this->temporaryFile->folder}/{$this->temporaryFile->folder}.jpeg", "uploads/{$this->temporaryFile->folder}.jpeg");
        Storage::deleteDirectory("uploads/tmp/{$this->temporaryFile->folder}");
        $this->temporaryFile->delete();
    }
}
