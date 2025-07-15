<?php
namespace App\Jobs;

use App\Models\Clip;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public Clip $clip) {}

    public function handle(): void
    {
        $wav = storage_path('tmp/'.$this->clip->uuid.'.wav');
        File::ensureDirectoryExists(dirname($wav));

        $cmd = ['ffmpeg', '-y', '-i', $this->clip->video_path,
            '-ar', '16000', '-ac', '1', '-c:a', 'pcm_s16le', $wav];

        (new Process($cmd))->setTimeout(300)->mustRun();

        $this->clip->update([
            'wav_path' => $wav,
            'status'   => 'audio_done',
        ]);

        TranscribeJob::dispatch($this->clip)->onQueue('transcribe');
    }
}

