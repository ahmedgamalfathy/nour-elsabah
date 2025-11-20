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
        Schema::create('points_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('points_per_currency', 8, 2)->default(1); // كل 1 جنيه = كام نقطة
            $table->decimal('currency_per_point', 8, 2)->default(1); // كل نقطة = كام جنيه
            $table->integer('min_points_to_redeem')->default(100); // أقل نقاط للاستخدام
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_settings');
    }
};
