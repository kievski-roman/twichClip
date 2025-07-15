<?php

namespace App\Jobs;
use App\Models\Clip;
use App\Services\VideoDownloaderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Part\File;


class DownloadClipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Clip $clip) {}

    public function handle(VideoDownloaderService $downloader): void
    {
        // куди зберігаємо
        $mp4 = storage_path("app/public/videos/{$this->clip->uuid}.mp4");
        // якщо вже завантажено — пропускаємо
        if (! file_exists($mp4)) {
            $downloader->download($this->clip->url, $mp4);
        }

        $this->clip->update([
            'video_path' => $mp4,
            'status'     => 'video_done',
        ]);

        ConvertAudioJob::dispatch($this->clip)->onQueue('audio');
    }
}

