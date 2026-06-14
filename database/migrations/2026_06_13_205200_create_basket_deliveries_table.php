<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('basket_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_deliveries');
    }
};
