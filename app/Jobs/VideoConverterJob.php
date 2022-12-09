<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoConverterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $temporaryFile;
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
        FFMpeg::fromDisk('public')->open("uploads/tmp/{$this->temporaryFile->folder}/video.{$this->temporaryFile->extension}")->export()->toDisk('public')->inFormat(new \FFMpeg\Format\Video\X264())->save("uploads/{$this->temporaryFile->folder}.mp4");
        Storage::delete("uploads/tmp/{$this->temporaryFile->folder}/video.mp4");
        Storage::move("uploads/tmp/{$this->temporaryFile->folder}/{$this->temporaryFile->folder}.mp4", "uploads/{$this->temporaryFile->folder}.mp4");
        Storage::deleteDirectory("uploads/tmp/{$this->temporaryFile->folder}");
        $this->temporaryFile->delete();
    }
}
