<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorageSetting extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return self::rulesFor($this->input('storage'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesFor(?string $storage): array
    {
        $rules = [
            'storage' => 'required|in:local,digitalocean,aws_s3,wasabi,minio',
        ];

        switch ($storage) {
            case 'digitalocean':
                return array_merge($rules, [
                    'digitalocean_key' => 'required|string',
                    'digitalocean_secret_key' => 'required|string',
                    'digitalocean_bucket' => 'required|string',
                    'digitalocean_region' => 'required|string',
                ]);
            case 'aws_s3':
                return array_merge($rules, [
                    'aws_access_key' => 'required|string',
                    'aws_secret_key' => 'required|string',
                    'aws_bucket' => 'required|string',
                    'aws_region' => 'required|string',
                ]);
            case 'wasabi':
                return array_merge($rules, [
                    'wasabi_access_key' => 'required|string',
                    'wasabi_secret_key' => 'required|string',
                    'wasabi_bucket' => 'required|string',
                    'wasabi_region' => 'required|string',
                ]);
            case 'minio':
                return array_merge($rules, [
                    'minio_endpoint' => 'required|string',
                    'minio_access_key' => 'required|string',
                    'minio_secret_key' => 'required|string',
                    'minio_bucket' => 'required|string',
                    'minio_region' => 'required|string',
                ]);
            default:
                return $rules;
        }
    }
}
