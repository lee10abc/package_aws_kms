<?php

declare(strict_types=1);

namespace Jean\AwsKms\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Aws\Kms\KmsClient;
use Carbon\Carbon;

class AwsKmsService
{
    protected $region;
    protected $accessKeyId;
    protected $accessSecretKey;
    protected $keyId;
    protected $table;
    protected $nameColumn;
    protected $valueColumn;
    protected $keyName;

    protected KmsClient $kms;

    public function __construct()
    {
        $this->region = config('aws-kms.region', null);
        $this->accessKeyId = config('aws-kms.access_key_id', null);
        $this->accessSecretKey = config('aws-kms.access_secret_key', null);
        $this->keyId = config('aws-kms.key_id', null);
        $this->table = config('aws-kms.table', null);
        $this->nameColumn = config('aws-kms.name_column', null);
        $this->valueColumn = config('aws-kms.value_column', null);
        $this->keyName = config('aws-kms.key_name', null);

        $this->kms = new KmsClient([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => [
                'key' => $this->accessKeyId,
                'secret' => $this->accessSecretKey,
            ],
        ]);
    }

    public function encrypt(mixed $value): string
    {
        $stringValue = is_array($value) ? json_encode($value) : (string)$value;

        $this->verifyConfig();
        if (empty($stringValue)) { return ""; }

        $result = DB::selectOne(
            "SELECT AES_ENCRYPT(?, ?) AS encrypted",
            [$stringValue, AwsKmsService::getPlaintextKey()]
        );

        return $result->encrypted;
    }

    public function decrypt(string $value): string
    {
        $this->verifyConfig();
        if (empty($value)) { return ""; }

        $result = DB::selectOne(
            "SELECT AES_DECRYPT(?, ?) AS decrypted",
            [$value, AwsKmsService::getPlaintextKey()]
        );

        return $result?->decrypted ?? '';
    }

    public function encryptIv(mixed $value): string
    {
        $stringValue = is_array($value) ? json_encode($value) : (string)$value;

        $this->verifyConfig();
        if (empty($stringValue)) { return ""; }

        $iv = random_bytes(16);

        $result = DB::selectOne(
            "SELECT AES_ENCRYPT(?, ?, ?, 'AES-256-CBC') AS encrypted",
            [$stringValue, AwsKmsService::getPlaintextKey(), $iv]
        );

        return $iv . $result->encrypted;
    }

    public function decryptIv(string $value): string
    {
        $this->verifyConfig();
        if (empty($value)) { return ""; }

        $iv = substr($value, 0, 16);
        $encryptedData = substr($value, 16);

        $result = DB::selectOne(
            "SELECT AES_DECRYPT(?, ?, ?, 'AES-256-CBC') AS decrypted",
            [$encryptedData, AwsKmsService::getPlaintextKey(), $iv]
        );

        return $result?->decrypted ?? '';
    }

    public function generateKey(): array
    {
        return $this->kms->generateDataKey([
            'KeyId' => $this->keyId,
            'KeySpec' => 'AES_256',
        ])->toArray();
    }

    public static function getPlaintextKey(): string
    {
        return Cache::remember('kms_plaintext_key', Carbon::now()->addHours((int) trim(config('aws-kms.cache_hours', 6))), function () {
            $key = DB::table(config('aws-kms.table'))->where(config('aws-kms.name_column'), config('aws-kms.key_name'))->first();

            if (!$key) {
                throw new Exception("KMS encryption key not found.");
            }

            $kms = new KmsClient([
                'region' => config('aws-kms.region'),
                'version' => 'latest',
                'credentials' => [
                    'key' => config('aws-kms.access_key_id'),
                    'secret' => config('aws-kms.access_secret_key'),
                ],
            ]);

            $valueColumnName = config('aws-kms.value_column');
            $plainText = $kms->decrypt([
                'CiphertextBlob' => $key->{$valueColumnName},
            ])['Plaintext'];

            if (strlen($plainText) !== 32) {
                throw new Exception("Plaintext Key size must be 32 bytes");
            }

            return $plainText;
        });
    }

    private function verifyConfig(): void
    {
        foreach ([
            'region' => 'REGION',
            'accessKeyId' => 'ACCESS KEY ID',
            'accessSecretKey' => 'ACCESS SECRET KEY',
            'keyId' => 'KEY ID',
            'table' => 'TABLE',
            'nameColumn' => 'NAME COLUMN',
            'valueColumn' => 'VALUE COLUMN',
            'keyName' => 'KEY NAME',
        ] as $property => $label) {
            if (empty($this->{$property})) {
                throw new Exception("Missing {$label} in config.");
            }
        }
    }
}
