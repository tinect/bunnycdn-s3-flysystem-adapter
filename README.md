# Flysystem Adapter for BunnyCDN Storage with S3

> **⚠ ABONDED**
> BunnyCDN killed S3-Support for the moment, please use [tinect/bunnycdn-sftp-flysystem-adapter](https://github.com/tinect/bunnycdn-sftp-flysystem-adapter)

[![Test V1](https://github.com/tinect/bunnycdn-s3-flysystem-adapter/actions/workflows/test_v1.yml/badge.svg)](https://github.com/tinect/bunnycdn-s3-flysystem-adapter/actions/workflows/test_v1.yml)

This adapter supports Flysystem with version 1 for BunnyCDN.  

## Installation

```bash
composer require tinect/bunnycdn-s3-flysystem-adapter:^1.0
```

## Usage

```php
use League\Flysystem\Filesystem;
use Tinect\Flysystem\BunnyCDN\BunnyCDNAdapter;

$client = new BunnyCDNAdapter('storageName', 'api-key-or-ftp-passwort', 'storage.bunnycdn.com', 'optionalSubfolder');
$filesystem = new Filesystem($client);
```
