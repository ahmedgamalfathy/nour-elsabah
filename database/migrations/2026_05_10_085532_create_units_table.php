<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "كيلو", "جرام", "قطعة"
            $table->decimal('step', 8, 3)->default(1.000);  // minimum increment e.g. 0.250
            $table->timestamps();
        });

        // Seed default "piece" unit so existing products stay valid
        DB::table('units')->insert([
            'name'       => 'قطعة',
            'step'       => 1.000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
