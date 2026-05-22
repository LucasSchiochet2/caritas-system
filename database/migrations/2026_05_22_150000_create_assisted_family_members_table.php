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
        Schema::create('assisted_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_id')->constrained('parishes')->cascadeOnDelete();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->string('name');
            $table->string('mother_name');
            $table->string('relationship', 50);
            $table->unsignedTinyInteger('age');
            $table->string('registration_status', 100);
            $table->date('registration_date');
            $table->decimal('personal_income', 10, 2)->default(0);
            $table->boolean('is_responsible')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assisted_family_members');
    }
};
