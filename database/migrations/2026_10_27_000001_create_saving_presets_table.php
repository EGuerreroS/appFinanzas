<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('essential_pct');
            $table->unsignedTinyInteger('non_essential_pct');
            $table->unsignedTinyInteger('savings_pct');
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('saving_preset_id')->nullable()->after('estatus_id')->constrained('saving_presets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saving_preset_id');
        });

        Schema::dropIfExists('saving_presets');
    }
};
