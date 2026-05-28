<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorageSetting extends Model
{
    public const HASH_TEMP_FILE_TIME = 60;

    protected $table = 'storage_settings';

    protected $fillable = ['filesystem', 'auth_keys', 'status'];

    public const AWS_REGIONS = [
        'us-east-2' => 'US East (Ohio) us-east-2',
        'us-east-1' => 'US East (N. Virginia) us-east-1',
        'us-west-1' => 'US West (N. California) us-west-1',
        'us-west-2' => 'US West (Oregon) us-west-2',
        'af-south-1' => 'Africa (Cape Town) af-south-1',
        'ap-east-1' => 'Asia Pacific (Hong Kong) ap-east-1',
        'ap-south-1' => 'Asia Pacific (Mumbai) ap-south-1',
        'ap-northeast-3' => 'Asia Pacific (Osaka-Local) ap-northeast-3',
        'ap-northeast-2' => 'Asia Pacific (Seoul)  ap-northeast-2',
        'ap-southeast-1' => 'Asia Pacific (Singapore)  ap-southeast-1',
        'ap-southeast-2' => 'Asia Pacific (Sydney) ap-southeast-2',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)  ap-northeast-1',
        'ca-central-1' => 'Canada (Central) ca-central-1',
        'eu-central-1' => 'Europe (Frankfurt) eu-central-1',
        'eu-west-1' => 'Europe (Ireland) eu-west-1',
        'eu-west-2' => 'Europe (London)  eu-west-2',
        'eu-south-1' => 'Europe (Milan) eu-south-1',
        'eu-west-3' => 'Europe (Paris) eu-west-3',
        'eu-north-1' => 'Europe (Stockholm) eu-north-1',
        'me-south-1' => 'Middle East (Bahrain) me-south-1',
        'me-central-1' => 'Middle East (UAE) (me-central-1)',
        'sa-east-1' => 'South America (São Paulo)   sa-east-1',
    ];

    public const DIGITALOCEAN_REGIONS = [
        'nyc1' => 'New York City, United States',
        'nyc3' => 'New York City, United States',
        'ams3' => 'Amsterdam, the Netherlands',
        'sfo3' => 'San Francisco, United States',
        'sgp1' => 'Singapore',
        'lon1' => 'London, United Kingdom',
        'fra1' => 'Frankfurt, Germany',
        'tor1' => 'Toronto, Canada',
        'blr1' => 'Bangalore, India',
        'syd1' => 'Sydney, Australia',
    ];

    public const WASABI_REGIONS = [
        'ap-southeast-1' => 'AP Southeast 1 (Singapore)',
        'ap-southeast-2' => 'AP Southeast 2 (Sydney)',
        'ap-northeast-2' => 'AP Northeast 2 (Osaka)',
        'ap-northeast-1' => 'AP Northeast 1 (Tokyo)',
        'eu-west-3' => 'EU West 3 (London)',
        'eu-west-2' => 'EU West 2 (Paris)',
        'eu-west-1' => 'EU West 1 (London)',
        'eu-south-1' => 'EU South 1 (Milan)',
        'eu-central-2' => 'EU Central 2 (Frankfurt)',
        'eu-central-1' => 'EU Central 1 (Amsterdam)',
        'ca-central-1' => 'CA Central 1 (Toronto)',
        'us-west-1' => 'US West 1 (Oregon)',
        'us-central-1' => 'US Central 1 (Texas)',
        'us-east-2' => 'US East 2 (N. Virginia)',
        'us-east-1' => 'US East 1 (N. Virginia)',
    ];

    public const S3_COMPATIBLE_STORAGE = ['aws_s3', 'digitalocean', 'wasabi', 'minio'];

    /**
     * Configure Laravel's s3 disk for S3-compatible providers (AWS, DO Spaces, Wasabi, MinIO).
     */
    public static function applyS3CompatibleConfig(string $filesystem, ?string $authKeysJson): void
    {
        if (! $authKeysJson) {
            return;
        }

        $auth = json_decode($authKeysJson);
        if (! $auth || ! is_object($auth)) {
            return;
        }

        $key = $auth->access_key ?? $auth->key ?? '';
        $secret = $auth->secret_key ?? $auth->secret ?? '';
        $region = $auth->region ?? 'us-east-1';
        $bucket = $auth->bucket ?? '';

        $endpoint = null;
        $usePathStyle = false;

        switch ($filesystem) {
            case 'aws':
            case 'aws_s3':
                break;
            case 'digitalocean':
                $endpoint = 'https://'.$region.'.digitaloceanspaces.com';
                break;
            case 'wasabi':
                $endpoint = 'https://s3.'.$region.'.wasabisys.com';
                break;
            case 'minio':
                $endpoint = $auth->endpoint ?? '';
                $usePathStyle = true;
                break;
            default:
                return;
        }

        $disk = [
            'driver' => 's3',
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
            'bucket' => $bucket,
            'url' => null,
            'use_path_style_endpoint' => $usePathStyle,
            'throw' => false,
        ];

        if ($endpoint) {
            $disk['endpoint'] = $endpoint;
        }

        foreach ($disk as $k => $v) {
            config(["filesystems.disks.s3.{$k}" => $v]);
        }

        config([
            'filesystems.default' => 's3',
            'filesystems.cloud' => 's3',
        ]);
    }
}
