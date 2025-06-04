<?php

declare(strict_types=1);

return [
    'region' => env('AWS_KMS_REGION'),
    'access_key_id' => env('AWS_KMS_ACCESS_KEY_ID'),
    'access_secret_key' => env('AWS_KMS_ACCESS_SECRET_KEY'),
    'key_id' => env('AWS_KMS_KEY_ID'),
    'cache_hours' => env('AWS_KMS_CACHE_HOURS'),
    'table' => env('AWS_KMS_DB_TABLE'),
    'name_column' => env('AWS_KMS_DB_NAME_COLUMN'),
    'value_column' => env('AWS_KMS_DB_VALUE_COLUMN'),
    'key_name' => env('AWS_KMS_DB_KEY_NAME'),
];
