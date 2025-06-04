<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);
pest()->extend(Tests\TestCase::class);

test('[PACKAGE][AWS_KMS] - ', function (): void {
})->skip();
