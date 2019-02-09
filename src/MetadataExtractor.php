<?php

namespace App;

class MetadataExtractor
{
    protected $filePath;
    protected $exifData;

    public static function from($filePath)
    {
        $instance = new self();
        $instance->filePath = $filePath;
        $instance->extract();
        return $instance;
    }

    protected function extract()
    {
        if (!file_exists($this->filePath)) {
            throw new \Exception("File not found: {$this->filePath}");
        }

        $this->exifData = @exif_read_data($this->filePath, 0, true);
    }

    public function getAll()
    {
        return $this->exifData;
    }

    public function getCamera()
    {
        $make = $this->exifData['IFD0']['Make'] ?? '';
        $model = $this->exifData['IFD0']['Model'] ?? '';

        return trim("{$make} {$model}");
    }

    public function getGPS()
    {
        if (!isset($this->exifData['GPS'])) {
            return null;
        }

        $gps = $this->exifData['GPS'];

        if (!isset($gps['GPSLatitude']) || !isset($gps['GPSLongitude'])) {
            return null;
        }

        $lat = $this->convertGPSCoordinate($gps['GPSLatitude'], $gps['GPSLatitudeRef']);
        $lng = $this->convertGPSCoordinate($gps['GPSLongitude'], $gps['GPSLongitudeRef']);

        return [
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    protected function convertGPSCoordinate($coordinate, $ref)
    {
        $degrees = count($coordinate) > 0 ? $this->gpsToDecimal($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->gpsToDecimal($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->gpsToDecimal($coordinate[2]) : 0;

        $value = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($ref == 'S' || $ref == 'W') {
            $value = -$value;
        }

        return $value;
    }

    protected function gpsToDecimal($coordinate)
    {
        $parts = explode('/', $coordinate);
        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return (float) $parts[0];
        return (float) $parts[0] / (float) $parts[1];
    }

    public function getExposure()
    {
        $aperture = $this->exifData['EXIF']['FNumber'] ?? 'N/A';
        $shutter = $this->exifData['EXIF']['ExposureTime'] ?? 'N/A';
        $iso = $this->exifData['EXIF']['ISOSpeedRatings'] ?? 'N/A';

        return [
            'aperture' => $aperture,
            'shutter' => $shutter,
            'iso' => $iso
        ];
    }

    public function getDateTime()
    {
        return $this->exifData['EXIF']['DateTimeOriginal'] ?? null;
    }

    public function toJson()
    {
        return json_encode([
            'camera' => $this->getCamera(),
            'gps' => $this->getGPS(),
            'exposure' => $this->getExposure(),
            'datetime' => $this->getDateTime(),
            'all' => $this->exifData
        ], JSON_PRETTY_PRINT);
    }

    public function strip($outputPath)
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception("Imagick extension required for stripping metadata");
        }

        $image = new \Imagick($this->filePath);
        $image->stripImage();
        $image->writeImage($outputPath);
        $image->destroy();

        return $outputPath;
    }
}
