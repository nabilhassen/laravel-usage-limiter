<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('limits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plan')->nullable();
            $table->double('allowed_amount');
            $table->string('reset_frequency')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'plan']);
        });

        Schema::create('limitables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('limit_id')->nullable()->references('id')->on('limits')->cascadeOnDelete()->cascadeOnUpdate();
            $table->morphs('limitable');
            $table->double('used_amount');

            $table->unique(['limitable_type', 'limitable_id', 'limit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limitables');
        Schema::dropIfExists('limits');
    }
};
