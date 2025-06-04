<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Aws\Kms\KmsClient;
use Illuminate\Support\Facades\DB;
use Jean\AwsKms\Services\AwsKmsService;
use Carbon\Carbon;

class KmsEncryptionKeySeeder extends Seeder
{
    public function run(): void
    {
        $service = app(AwsKmsService::class);

        $exists = DB::table(config('aws-kms.table'))->where(config('aws-kms.name_column'), config('aws-kms.key_name'))->exists();
        if ($exists) {
            $this->command->info('KMS encryption key already exists.');
            return;
        }

        $dataKey = $service->generateKey();

        DB::table(config('aws-kms.table'))->insert([
            config('aws-kms.name_column') => config('aws-kms.key_name'),
            config('aws-kms.value_column') => $dataKey['CiphertextBlob'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('KMS encryption key generated and stored.');
    }
}
