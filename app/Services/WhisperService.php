<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WhisperService
{
    public function transcribe(string $input, ?string $lang = null): ?string
    {
        $bin = config('services.whisper.model_path');
        $model = config('services.whisper.model');
        $lang  = $lang ?? null;

        if (! file_exists($bin) || ! file_exists($model)) {
            throw new \RuntimeException('Whisper bin or model not found');
        }

        $out = storage_path('app/subtitles/'.Str::uuid().'.srt');

        $proc = new Process([
            $bin,
            $input,
            '--model', $model,
            '--language', $lang,
            '--output-srt',
            '--output-file', pathinfo($out, PATHINFO_FILENAME),

        ]);

        $proc->setTimeout(300);

        try {
            $proc->mustRun();
            return $out;
        } catch (ProcessFailedException $e) {
            logger()->error('Whisper failed', ['err' => $e->getMessage()]);
            return null;
        }
    }


}

