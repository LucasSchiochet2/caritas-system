<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_inventory_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['basket_template_id', 'parish_inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_template_items');
    }
};
