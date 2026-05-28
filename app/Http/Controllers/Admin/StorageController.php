<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\UpdateStorageSetting;
use App\StorageSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StorageController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.storageSetting');
        $this->pageIcon = 'ti-settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $rows = StorageSetting::all()->keyBy('filesystem');

        $this->local = $rows->get('local');

        $enabled = StorageSetting::where('status', 'enabled')->first();
        $selected = $enabled->filesystem ?? 'local';
        if ($selected === 'aws') {
            $selected = 'aws_s3';
        }
        $this->selected_storage = $selected;

        $this->digitalocean_key = '';
        $this->digitalocean_secret_key = '';
        $this->digitalocean_bucket = '';
        $this->digitalocean_region = 'nyc3';

        $this->aws_access_key = '';
        $this->aws_secret_key = '';
        $this->aws_bucket = '';
        $this->aws_region = 'us-east-1';

        $this->wasabi_access_key = '';
        $this->wasabi_secret_key = '';
        $this->wasabi_bucket = '';
        $this->wasabi_region = 'eu-central-1';

        $this->minio_endpoint = '';
        $this->minio_access_key = '';
        $this->minio_secret_key = '';
        $this->minio_bucket = '';
        $this->minio_region = 'us-east-1';

        $this->hydrateFromAuthKeys($rows->get('digitalocean'), function ($keys) {
            $this->digitalocean_key = $keys->access_key ?? $keys->key ?? '';
            $this->digitalocean_secret_key = $keys->secret_key ?? $keys->secret ?? '';
            $this->digitalocean_bucket = $keys->bucket ?? '';
            $this->digitalocean_region = $keys->region ?? 'nyc3';
        });

        $s3Row = $rows->get('aws_s3') ?? $rows->get('aws');
        $this->hydrateFromAuthKeys($s3Row, function ($keys) {
            $this->aws_access_key = $keys->access_key ?? $keys->key ?? '';
            $this->aws_secret_key = $keys->secret_key ?? $keys->secret ?? '';
            $this->aws_bucket = $keys->bucket ?? '';
            $this->aws_region = $keys->region ?? 'us-east-1';
        });

        $this->hydrateFromAuthKeys($rows->get('wasabi'), function ($keys) {
            $this->wasabi_access_key = $keys->access_key ?? $keys->key ?? '';
            $this->wasabi_secret_key = $keys->secret_key ?? $keys->secret ?? '';
            $this->wasabi_bucket = $keys->bucket ?? '';
            $this->wasabi_region = $keys->region ?? 'eu-central-1';
        });

        $this->hydrateFromAuthKeys($rows->get('minio'), function ($keys) {
            $this->minio_endpoint = $keys->endpoint ?? '';
            $this->minio_access_key = $keys->access_key ?? $keys->key ?? '';
            $this->minio_secret_key = $keys->secret_key ?? $keys->secret ?? '';
            $this->minio_bucket = $keys->bucket ?? '';
            $this->minio_region = $keys->region ?? 'us-east-1';
        });

        return view('admin.storage-setting.index', $this->data);
    }

    private function hydrateFromAuthKeys(?StorageSetting $row, callable $apply): void
    {
        if (! $row || ! $row->auth_keys) {
            return;
        }
        try {
            $keys = json_decode($row->auth_keys, false, 512, JSON_THROW_ON_ERROR);
            if (is_object($keys)) {
                $apply($keys);
            }
        } catch (\Throwable $e) {
            $row->auth_keys = null;
            $row->save();
        }
    }

    public function store(UpdateStorageSetting $request)
    {
        $type = $request->storage;

        StorageSetting::query()->update(['status' => 'disabled']);

        $row = StorageSetting::firstOrNew(['filesystem' => $type === 'aws_s3' ? 'aws_s3' : $type]);

        if ($type === 'local') {
            $row->auth_keys = null;
        } else {
            $row->auth_keys = json_encode($this->authPayload($type, $request));
        }

        $row->filesystem = $type === 'aws_s3' ? 'aws_s3' : $type;
        $row->status = 'enabled';
        $row->save();

        session()->forget('storage_setting');

        return Reply::success(__('messages.noteUpdateSuccess'));
    }

    /**
     * @return array<string, mixed>
     */
    private function authPayload(string $type, Request $request): array
    {
        return match ($type) {
            'digitalocean' => [
                'driver' => 's3',
                'access_key' => $request->digitalocean_key,
                'secret_key' => $request->digitalocean_secret_key,
                'region' => $request->digitalocean_region,
                'bucket' => $request->digitalocean_bucket,
            ],
            'wasabi' => [
                'driver' => 's3',
                'access_key' => $request->wasabi_access_key,
                'secret_key' => $request->wasabi_secret_key,
                'region' => $request->wasabi_region,
                'bucket' => $request->wasabi_bucket,
            ],
            'aws_s3' => [
                'driver' => 's3',
                'access_key' => $request->aws_access_key,
                'secret_key' => $request->aws_secret_key,
                'region' => $request->aws_region,
                'bucket' => $request->aws_bucket,
            ],
            'minio' => [
                'driver' => 's3',
                'access_key' => $request->minio_access_key,
                'secret_key' => $request->minio_secret_key,
                'region' => $request->minio_region,
                'bucket' => $request->minio_bucket,
                'endpoint' => $request->minio_endpoint,
            ],
            default => [],
        };
    }

    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), UpdateStorageSetting::rulesFor($request->input('storage')));

        if ($validator->fails()) {
            return Reply::formErrors($validator);
        }

        $type = $request->storage;

        if ($type === 'local') {
            return Reply::error(__('messages.storageTestLocalSkipped'));
        }

        $json = json_encode($this->authPayload($type, $request));
        StorageSetting::applyS3CompatibleConfig($type === 'aws_s3' ? 'aws_s3' : $type, $json);

        $path = '.recruit-storage-test-'.uniqid('', true);

        try {
            Storage::disk('s3')->put($path, 'ok');
            Storage::disk('s3')->delete($path);
        } catch (\Throwable $e) {
            return Reply::error(__('messages.storageConnectionFailed').': '.$e->getMessage());
        }

        return Reply::success(__('messages.storageConnectionSuccess'));
    }
}
