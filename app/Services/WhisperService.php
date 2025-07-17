<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WhisperService
{
    
    public function transcribe(string $wav, ?string $lang = null): ?string
    {
       
        $bin   = config('services.whisper.bin_path');    // exe / cli
        $model = config('services.whisper.model_path');  // *.bin
        $lang  = $lang ?: 'auto';

        
        if (!is_file($bin) || !is_file($model)) {
            logger()->error('Whisper paths invalid', compact('bin', 'model'));
            return null;
        }

     
        $outDir = storage_path('app/subtitles');
        File::ensureDirectoryExists($outDir);

        $outBase = $outDir.'/'.pathinfo($wav, PATHINFO_FILENAME);

       
        $process = new Process([
            $bin,
            '-f', $wav,
            '-m', $model,
            '-l', $lang,
            '-osrt',          //  SRT
            '-of', $outBase,   
        ]);

        try {
            $process->setTimeout(300)->mustRun();
        } catch (ProcessFailedException $e) {
            logger()->error('whisper failed', [
                'stderr' => $e->getProcess()->getErrorOutput(),
            ]);
            return null;
        }

       
        $srt = $outBase.'.srt';
    
        return is_file($srt) ? $srt : null;
    }
}

