# Flysystem Adapter for BunnyCDN Storage with S3

This adapter supports Flysystem with version 1 for BunnyCDN.  
There is a dedicated repository for [Flysystem 2 support](https://github.com/tinect/bunnycdn-s3-flysystem2-adapter)

## Installation

```bash
composer require tinect/bunnycdn-s3-flysystem-adapter
```

## Usage

```php
use League\Flysystem\Filesystem;
use Tinect\Flysystem\BunnyCDN\BunnyCDNAdapter;

$client = new BunnyCDNAdapter('storageName', 'api-key-or-ftp-passwort', 'storage.bunnycdn.com', 'optionalSubfolder');
$filesystem = new Filesystem($client);
```
