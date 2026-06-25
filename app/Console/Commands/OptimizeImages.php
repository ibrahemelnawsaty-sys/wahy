<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--path=public/images}';

    protected $description = 'Convert images to WebP format for better performance';

    public function handle()
    {
        $path = $this->option('path');

        if (! File::exists($path)) {
            $this->error("Path {$path} does not exist!");

            return 1;
        }

        $this->info("Optimizing images in {$path}...");

        $files = File::glob("{$path}/**/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
        $converted = 0;

        foreach ($files as $file) {
            try {
                $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);

                if (File::exists($webpPath)) {
                    $this->line("Skipping {$file} (WebP exists)");

                    continue;
                }

                // Using GD or Imagick
                if (extension_loaded('gd')) {
                    $this->convertWithGD($file, $webpPath);
                } else {
                    $this->line("GD extension not loaded, skipping {$file}");

                    continue;
                }

                $originalSize = File::size($file);
                $webpSize = File::size($webpPath);
                $savings = round((1 - $webpSize / $originalSize) * 100, 1);

                $this->info("✓ {$file} → {$webpPath} (Saved {$savings}%)");
                $converted++;

            } catch (\Exception $e) {
                $this->error("✗ Failed to convert {$file}: " . $e->getMessage());
            }
        }

        $this->info("\nConverted {$converted} images to WebP format!");

        return 0;
    }

    private function convertWithGD($source, $destination)
    {
        $info = getimagesize($source);

        if ($info === false) {
            throw new \Exception('Not a valid image');
        }

        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                throw new \Exception('Unsupported image type: ' . $info['mime']);
        }

        imagewebp($image, $destination, 85);
        imagedestroy($image);
    }
}
