<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_inventory_item_quantity_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_delivery_items');
    }
};
