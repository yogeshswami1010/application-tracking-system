<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits;

use App\StorageSetting;

trait FileSystemSettingTrait
{
    public function setFileSystemConfigs()
    {
        $settings = storage_setting();

        if (! $settings) {
            return;
        }

        switch ($settings->filesystem) {
            case 'local':
                config(['filesystems.default' => 'local']);
                break;
            case 'aws':
            case 'aws_s3':
            case 'digitalocean':
            case 'wasabi':
            case 'minio':
                StorageSetting::applyS3CompatibleConfig($settings->filesystem, $settings->auth_keys);
                break;
        }
    }
}
