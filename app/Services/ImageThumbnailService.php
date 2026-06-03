<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageThumbnailService
{
    private ?string $ffmpegBinary = null;

    public function generate(string $relativeImagePath, string $disk = 'public'): ?string
    {
        $ffmpeg = $this->getFfmpegBinary();
        if ($ffmpeg === null) {
            Log::warning('FFmpeg no esta disponible para generar miniaturas de imagen.');
            return null;
        }

        $imagePath = storage_path("app/{$disk}/{$relativeImagePath}");
        if (!file_exists($imagePath)) {
            Log::warning("No se encontro la imagen para generar miniatura: {$imagePath}");
            return null;
        }

        $thumbnailRelativePath = $this->thumbnailPathFor($relativeImagePath);
        $thumbnailPath = storage_path("app/{$disk}/{$thumbnailRelativePath}");

        if (!is_dir(dirname($thumbnailPath))) {
            mkdir(dirname($thumbnailPath), 0755, true);
        }

        $command = sprintf(
            '%s -y -i %s -vf %s -frames:v 1 -quality 80 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($imagePath),
            escapeshellarg('scale=600:600:force_original_aspect_ratio=increase,crop=600:600'),
            escapeshellarg($thumbnailPath)
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($thumbnailPath)) {
            Log::warning('No se pudo generar miniatura de imagen.', [
                'image' => $imagePath,
                'output' => $output,
                'resultCode' => $resultCode,
            ]);
            return null;
        }

        return 'storage/' . str_replace('\\', '/', $thumbnailRelativePath);
    }

    public function generatePreview(string $relativeImagePath, string $disk = 'public'): ?string
    {
        $ffmpeg = $this->getFfmpegBinary();
        if ($ffmpeg === null) {
            Log::warning('FFmpeg no esta disponible para generar preview de imagen.');
            return null;
        }

        $imagePath = storage_path("app/{$disk}/{$relativeImagePath}");
        if (!file_exists($imagePath)) {
            Log::warning("No se encontro la imagen para generar preview: {$imagePath}");
            return null;
        }

        $previewRelativePath = $this->previewPathFor($relativeImagePath);
        $previewPath = storage_path("app/{$disk}/{$previewRelativePath}");

        if (!is_dir(dirname($previewPath))) {
            mkdir(dirname($previewPath), 0755, true);
        }

        $command = sprintf(
            '%s -y -i %s -vf %s -frames:v 1 -quality 85 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($imagePath),
            escapeshellarg('scale=1400:1400:force_original_aspect_ratio=decrease'),
            escapeshellarg($previewPath)
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($previewPath)) {
            Log::warning('No se pudo generar preview de imagen.', [
                'image' => $imagePath,
                'output' => $output,
                'resultCode' => $resultCode,
            ]);
            return null;
        }

        return 'storage/' . str_replace('\\', '/', $previewRelativePath);
    }

    private function thumbnailPathFor(string $relativeImagePath): string
    {
        $directory = pathinfo($relativeImagePath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativeImagePath, PATHINFO_FILENAME);

        return "{$directory}/Thumbnails/{$filename}.webp";
    }

    private function previewPathFor(string $relativeImagePath): string
    {
        $directory = pathinfo($relativeImagePath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativeImagePath, PATHINFO_FILENAME);

        return "{$directory}/Previews/{$filename}.webp";
    }

    private function getFfmpegBinary(): ?string
    {
        if ($this->ffmpegBinary !== null) {
            return $this->ffmpegBinary;
        }

        $candidates = array_filter([
            env('FFMPEG_PATH'),
            'ffmpeg',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
        ]);

        foreach ($candidates as $candidate) {
            exec(escapeshellarg($candidate) . ' -version 2>&1', $output, $resultCode);
            if ($resultCode === 0) {
                $this->ffmpegBinary = $candidate;
                return $this->ffmpegBinary;
            }
        }

        return null;
    }
}
