<?php

namespace App\Console\Commands;

use App\Models\BannerTranslation;
use App\Models\CategoryTranslation;
use App\Models\ProductImage;
use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateResponsiveImages extends Command
{
    protected $signature = 'images:responsive {--dry-run : List the images that would be processed without writing files}';

    protected $description = 'Create WebP size variants next to existing product, banner and category images (originals are never changed or deleted)';

    public function handle(ImageOptimizationService $images): int
    {
        if (! extension_loaded('gd') && ! $this->option('dry-run')) {
            $this->error('The PHP GD extension is not enabled, so no WebP files can be created. Install/enable php-gd and run again.');

            return self::FAILURE;
        }

        $paths = collect()
            ->merge(ProductImage::query()->pluck('image_url'))
            ->merge(BannerTranslation::query()->pluck('image_url'))
            ->merge(CategoryTranslation::query()->pluck('image_url'))
            ->filter(fn ($path) => is_string($path) && $path !== '' && ! filter_var($path, FILTER_VALIDATE_URL))
            ->map(fn ($path) => ltrim($path, '/'))
            ->unique()
            ->values();

        $disk = Storage::disk('public');
        $done = 0;
        $skipped = 0;

        foreach ($paths as $path) {
            if (! $disk->exists($path)) {
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line('would process: '.$path);
                $done++;
                continue;
            }

            $created = $images->generateResponsiveImages($path);
            $this->line(($created ? 'ok      ' : 'skipped ').$path);
            $created ? $done++ : $skipped++;
        }

        $this->info(($this->option('dry-run') ? 'Would process ' : 'Processed ').$done.' image(s); skipped '.$skipped.'.');

        return self::SUCCESS;
    }
}
