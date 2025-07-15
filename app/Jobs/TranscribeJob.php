<?php

namespace App\Jobs;

use App\Models\Clip;
use App\Services\WhisperService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;

class TranscribeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Clip $clip) {}

    /**
     * Execute the job.
     */

    public function handle(WhisperService $whisper): void
    {
        $srt = $whisper->transcribe($this->clip->wav_path, $this->clip->lang);

        // ⬇︎ 1. Якщо файл не створився – відмічаємо failed і виходимо
        if (!$srt || !is_file($srt)) {
            $this->clip->update(['status' => 'failed']);
            return;
        }

        // ⬇︎ 2. (Опційно) переносимо у публічну директорію
        $publicSrt = storage_path("app/public/str/{$this->clip->uuid}.srt");
        //creates a folder if it does not exist and if exists, then does nothing
        File::ensureDirectoryExists(dirname($publicSrt));
        if (is_file($publicSrt)) {
            @unlink($publicSrt);
        }

        File::move($srt, $publicSrt);           // або copy, якщо хочеш лишити оригінал

        // ⬇︎ 3. Оновлюємо БД
        $this->clip->update([
            'srt_path'         => $publicSrt,
            'transcript_plain' => strip_tags(file_get_contents($publicSrt)),
            'status'           => 'ready',
        ]);

        // ⬇︎ 4. Чистимо тимчасовий WAV
        @unlink($this->clip->wav_path);
    }

}
