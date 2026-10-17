<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_acquisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->timestamps();

            $table->index('utm_campaign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_acquisitions');
    }
};
