<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_templates');
    }
};
