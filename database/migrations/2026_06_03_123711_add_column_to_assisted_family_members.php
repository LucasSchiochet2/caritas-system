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
        Schema::table('assisted_family_members', function (Blueprint $table) {
            $table->string('cpf')->nullable()->after('name')->unique();
            $table->date('birth_date')->nullable()->after('cpf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assisted_family_members', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn('cpf');
            $table->dropColumn('birth_date');
        });
    }
};
