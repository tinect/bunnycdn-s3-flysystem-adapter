<?php declare(strict_types=1);

namespace Tinect\Flysystem\BunnyCDN;

use AsyncAws\Core\Configuration;
use AsyncAws\Flysystem\S3\AsyncAwsS3Adapter;
use AsyncAws\S3\S3Client;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;

class BunnyCDNAdapter extends AsyncAwsS3Adapter
{
    use NotSupportingVisibilityTrait;

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

    public function copy($path, $newpath): bool
    {
        if ($content = $this->read($path)) {
            $this->write($newpath, $content['contents'], new Config());
            return true;
        }

        return false;
    }

    /*
     * we need to catch here while S3 results in Exception when deleting a not existing resource
     */
    public function delete($path): bool
    {
        try {
            return parent::delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteDir($path): bool
    {
        return $this->delete(rtrim($path, '/') . '/');
    }
}
