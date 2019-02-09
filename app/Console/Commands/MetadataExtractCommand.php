<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MetadataExtractor;

class MetadataExtractCommand extends Command
{
    protected $signature = 'metadata:extract {file}';
    protected $description = 'Extract metadata from image';

    public function handle()
    {
        $file = $this->argument('file');

        try {
            $metadata = MetadataExtractor::from($file);

            $this->info("Camera: " . $metadata->getCamera());

            $gps = $metadata->getGPS();
            if ($gps) {
                $this->info("GPS: {$gps['lat']}, {$gps['lng']}");
            }

            $exposure = $metadata->getExposure();
            $this->info("Exposure: f/{$exposure['aperture']}, {$exposure['shutter']}s, ISO {$exposure['iso']}");

            $this->line("\nFull JSON:");
            $this->line($metadata->toJson());

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
