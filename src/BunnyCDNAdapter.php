<?php
declare(strict_types=1);

namespace Tinect\Flysystem\BunnyCDN;

use AsyncAws\Core\Configuration;
use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class BunnyCDNAdapter extends AsyncAwsS3Adapter
{
    /**
     * This is used in tests. Don't remove it!
     * @var string[]
     */
    public const EXTRA_METADATA_FIELDS = [
        'Metadata',
        'StorageClass',
        'ETag',
        'VersionId',
    ];

    public function __construct($storageName, $apiKey, $endpoint, $subfolder = '')
    {
        if ($subfolder !== '') {
            $subfolder = rtrim($subfolder, '/') .  '/';
        }

        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'https://' . $endpoint;
        }

        $s3client = new S3Client([
            Configuration::OPTION_REGION  => '',
            Configuration::OPTION_ENDPOINT => rtrim($endpoint, '/'),
            Configuration::OPTION_SEND_CHUNKED_BODY => false,
            Configuration::OPTION_ACCESS_KEY_ID => $storageName,
            Configuration::OPTION_SECRET_ACCESS_KEY => $apiKey,
            Configuration::OPTION_PATH_STYLE_ENDPOINT => true
        ]);

        parent::__construct($s3client, $storageName, $subfolder);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        if (!$this->fileExists($source)) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }

        $this->write($destination, $this->read($source), new Config());
    }

    public function visibility(string $path): FileAttributes
    {
        if (!$this->fileExists($path)) {
            throw UnableToRetrieveMetadata::visibility($path);
        }

        return new FileAttributes($path, null, Visibility::PUBLIC);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'not supported!');
    }

    /*
     * BunnyCDN doesn't give mimeType, so we need to get it on our own
     */
    public function mimeType(string $path): FileAttributes
    {
        if (!$this->fileExists($path)) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        $detector = new ExtensionMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($path);

        if ($mimeType === null || $mimeType === '') {
            $detector = new FinfoMimeTypeDetector();
            $mimeType = $detector->detectMimeType($path, $this->read($path));
        }

        if ($mimeType === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return new FileAttributes(
            $path,
            null,
            null,
            null,
            $mimeType
        );
    }

    /*
     * TODO: check the reason. Maybe the timezone is missing to be calculated correctly
     */
    public function lastModified(string $path): FileAttributes
    {
        $result = parent::lastModified($path)->jsonSerialize();
        $result[StorageAttributes::ATTRIBUTE_LAST_MODIFIED] += 7200;

        return FileAttributes::fromArray($result);
    }

    /*
     * we need to catch here while S3 results in Exception when deleting a not existing resource
     */
    public function delete($path): void
    {
        try {
            parent::delete($path);
        } catch (UnableToDeleteFile $e) {
        }
    }

    public function deleteDir($path): void
    {
        $this->delete(rtrim($path, '/') . '/');
    }
}
