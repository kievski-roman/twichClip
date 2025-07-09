<?php


namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoDownloaderService
{
    public function downloadClip(string $clipUrl, string $filename): bool
    {
        $outputPath = storage_path('app/public/videos/' . $filename);

        $process = new Process([
            'yt-dlp',
            '-o', $outputPath,
            $clipUrl,
        ]);

        $process->setTimeout(70); // 3 хв максимум

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            logger()->error('yt-dlp failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
