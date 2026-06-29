<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminController;
use App\Models\Product;
use Illuminate\Console\Command;

class GenerateProductThumbnails extends Command
{
    protected $signature = 'images:thumbnails {--dry-run : List products needing a thumbnail without creating one} {--max=1000 : Thumbnail longest-edge in pixels} {--force : Regenerate even if a thumbnail already exists}';

    protected $description = 'Generate card-sized thumbnails ({base}_thumb.webp) for existing product cover images so product cards load instantly.';

    public function handle(): int
    {
        $max   = max(1, (int) $this->option('max'));
        $dry   = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $created = 0;
        $skipped = 0;

        Product::whereNotNull('image_url')->select(['id', 'image_url'])->chunkById(200, function ($products) use ($max, $dry, $force, &$created, &$skipped) {
            foreach ($products as $product) {
                $thumbRel = Product::thumbUrlFor($product->image_url);
                if (!$thumbRel) {
                    $skipped++; // external URL or non-webp
                    continue;
                }

                $fullMain  = public_path(ltrim($product->image_url, '/'));
                $fullThumb = public_path(ltrim($thumbRel, '/'));

                if (!file_exists($fullMain)) {
                    $this->warn("  missing source for product #{$product->id}: {$product->image_url}");
                    $skipped++;
                    continue;
                }

                if (!$force && file_exists($fullThumb)) {
                    $skipped++;
                    continue;
                }

                if ($dry) {
                    $this->line('would create  ' . $thumbRel);
                    $created++;
                    continue;
                }

                if ($this->makeThumb($fullMain, $fullThumb, $max)) {
                    $this->info('created  ' . $thumbRel);
                    $created++;
                } else {
                    $this->warn('  failed  ' . $thumbRel);
                    $skipped++;
                }
            }
        });

        $this->newLine();
        $verb = $dry ? 'would be created' : 'created';
        $this->info("Thumbnails {$verb}: {$created}; skipped: {$skipped}.");

        return self::SUCCESS;
    }

    private function makeThumb(string $fullMain, string $fullThumb, int $max): bool
    {
        $info = @getimagesize($fullMain);
        if (!$info) {
            return false;
        }
        [$w, $h] = $info;

        $image = @imagecreatefromwebp($fullMain);
        if (!$image) {
            return false;
        }

        $ok = AdminController::downscaleGdToWebp($image, $w, $h, $fullThumb, $max, 85);
        imagedestroy($image);

        return $ok;
    }
}
