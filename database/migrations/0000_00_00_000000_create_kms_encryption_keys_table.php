<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('aws-kms.table'), function (Blueprint $table) {
            $table->id();
            $table->string(config('aws-kms.name_column'), 64)->unique();
            $table->addColumn('binary', config('aws-kms.value_column'), ['length' => 512]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('aws-kms.table'));
    }
};
