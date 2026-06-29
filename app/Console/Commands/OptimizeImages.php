<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : List oversized images without modifying them} {--max=1600 : Maximum longest-edge in pixels}';

    protected $description = 'Downscale already-uploaded images in public/images that exceed the size cap (re-encodes in place, preserving format).';

    /** Folders written by AdminController::convertToWebp(). */
    private const FOLDERS = ['products', 'categories', 'banners', 'hero', 'auth', 'about'];

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max'));
        $dry = (bool) $this->option('dry-run');

        $scanned = 0;
        $changed = 0;
        $savedBytes = 0;

        foreach (self::FOLDERS as $folder) {
            $dir = public_path('images/' . $folder);
            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '/*.{webp,jpg,jpeg,png,gif}', GLOB_BRACE) ?: [] as $path) {
                $info = @getimagesize($path);
                if (!$info) {
                    continue;
                }
                $scanned++;

                [$w, $h] = $info;
                if (max($w, $h) <= $max) {
                    continue;
                }

                $sizeBefore = filesize($path);
                $rel = 'images/' . $folder . '/' . basename($path);

                if ($dry) {
                    $this->line(sprintf('would resize  %-48s %dx%d  (%s)', $rel, $w, $h, $this->human($sizeBefore)));
                    $changed++;
                    continue;
                }

                if (!$this->resizeInPlace($path, $info['mime'], $w, $h, $max)) {
                    $this->warn('  failed: ' . $rel);
                    continue;
                }

                clearstatcache(true, $path);
                $sizeAfter = filesize($path);
                $savedBytes += max(0, $sizeBefore - $sizeAfter);
                $changed++;
                $this->info(sprintf('resized  %-48s %dx%d  %s -> %s', $rel, $w, $h, $this->human($sizeBefore), $this->human($sizeAfter)));
            }
        }

        $this->newLine();
        $verb = $dry ? 'would be resized' : 'resized';
        $this->info("Scanned {$scanned} image(s); {$changed} {$verb}.");
        if (!$dry && $savedBytes > 0) {
            $this->info('Disk saved: ' . $this->human($savedBytes));
        }

        return self::SUCCESS;
    }

    /**
     * Decode, downscale, and re-encode an image in place, preserving its original format.
     * WebP re-encoding reuses AdminController::downscaleGdToWebp so the logic stays in one place.
     */
    private function resizeInPlace(string $path, string $mime, int $w, int $h, int $max): bool
    {
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png'              => @imagecreatefrompng($path),
            'image/webp'             => @imagecreatefromwebp($path),
            'image/gif'              => @imagecreatefromgif($path),
            default                  => null,
        };

        if (!$image) {
            return false;
        }

        if (in_array($mime, ['image/png', 'image/gif'], true)) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // WebP: reuse the shared downscale+encode helper.
        if ($mime === 'image/webp') {
            $ok = \App\Http\Controllers\AdminController::downscaleGdToWebp($image, $w, $h, $path, $max, 80);
            imagedestroy($image);
            return $ok;
        }

        // Other formats: downscale here and re-encode in the same format to keep the filename valid.
        $scale = $max / max($w, $h);
        $newW  = max(1, (int) round($w * $scale));
        $newH  = max(1, (int) round($h * $scale));
        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($image);

        $ok = match ($mime) {
            'image/jpeg', 'image/jpg' => imagejpeg($resized, $path, 82),
            'image/png'              => imagepng($resized, $path, 6),
            'image/gif'              => imagegif($resized, $path),
            default                  => false,
        };
        imagedestroy($resized);

        return $ok;
    }

    private function human(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024) . ' KB';
        return $bytes . ' B';
    }
}
