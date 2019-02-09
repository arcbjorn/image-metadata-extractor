# Image Metadata Extractor

Extract and analyze EXIF, IPTC, and XMP metadata from images.

## Features

- Extract all EXIF data (camera, lens, exposure settings)
- GPS coordinates extraction and mapping
- IPTC keywords and copyright info
- XMP metadata parsing
- Batch processing support
- Export to JSON/CSV
- Privacy scrubbing (remove metadata)

## Usage

```bash
# Extract metadata
php artisan metadata:extract image.jpg

# Batch extract
php artisan metadata:batch photos/

# Remove all metadata
php artisan metadata:strip image.jpg --output=clean.jpg

# GPS map view
php artisan metadata:map photos/ --export=locations.json
```

## API

```php
$metadata = MetadataExtractor::from('photo.jpg');
$metadata->getCamera(); // Canon EOS 5D
$metadata->getGPS(); // [lat: 40.7128, lng: -74.0060]
$metadata->getExposure(); // f/2.8, 1/250s, ISO 100
```

## Requirements

- PHP 7.2+
- Laravel 5.8
- ExifTool binary
