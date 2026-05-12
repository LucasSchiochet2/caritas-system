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
        Schema::create('bazaar_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('suggested_price', 10, 2);
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('size', 50)->nullable();
            $table->string('gender', 50)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('condition', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bazaar_items');
    }
};