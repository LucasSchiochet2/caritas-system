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
        Schema::create('parish_inventory_item_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_inventory_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->date('valid_until');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parish_inventory_item_quantities');
    }
};
