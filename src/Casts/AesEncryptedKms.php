<?php

namespace Jean\AwsKms\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Jean\AwsKms\Services\AwsKmsService;

class AesEncryptedKms implements CastsAttributes
{
    protected AwsKmsService $kmsService;

    public function __construct()
    {
        $this->kmsService = new AwsKmsService();
    }

    public function get($model, string $key, $value, array $attributes): string
    {
        return $this->kmsService->decrypt($value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        return $this->kmsService->encrypt($value);
    }
}

