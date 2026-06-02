<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VideoThumbnailService
{
    public function generate(string $relativeVideoPath, string $disk = 'public'): ?string
    {
        if (!$this->isFfmpegAvailable()) {
            Log::warning('FFmpeg no esta disponible para generar miniaturas de video.');
            return null;
        }

        $videoPath = storage_path("app/{$disk}/{$relativeVideoPath}");
        if (!file_exists($videoPath)) {
            Log::warning("No se encontro el video para generar miniatura: {$videoPath}");
            return null;
        }

        $thumbnailRelativePath = $this->thumbnailPathFor($relativeVideoPath);
        $thumbnailPath = storage_path("app/{$disk}/{$thumbnailRelativePath}");

        if (!is_dir(dirname($thumbnailPath))) {
            mkdir(dirname($thumbnailPath), 0755, true);
        }

        $command = sprintf(
            'ffmpeg -y -ss 00:00:01 -i %s -frames:v 1 -q:v 2 %s 2>&1',
            escapeshellarg($videoPath),
            escapeshellarg($thumbnailPath)
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($thumbnailPath)) {
            Log::warning('No se pudo generar miniatura de video.', [
                'video' => $videoPath,
                'output' => $output,
                'resultCode' => $resultCode,
            ]);
            return null;
        }

        return 'storage/' . str_replace('\\', '/', $thumbnailRelativePath);
    }

    private function thumbnailPathFor(string $relativeVideoPath): string
    {
        $directory = pathinfo($relativeVideoPath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativeVideoPath, PATHINFO_FILENAME);

        return "{$directory}/Thumbnails/{$filename}.jpg";
    }

    private function isFfmpegAvailable(): bool
    {
        exec('ffmpeg -version 2>&1', $output, $resultCode);

        return $resultCode === 0;
    }
}
