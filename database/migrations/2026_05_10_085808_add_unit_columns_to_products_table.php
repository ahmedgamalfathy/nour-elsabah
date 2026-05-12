<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Change quantity from smallInteger to decimal(15,3)
            $table->decimal('quantity', 15, 3)->default(0)->change();

            // Minimum quantity the customer can order (e.g. 0.250 kg)
            $table->decimal('min_quantity', 8, 3)->default(1.000)->after('quantity');

            // Increment step (e.g. 0.250 means multiples of 250g)
            $table->decimal('quantity_step', 8, 3)->default(1.000)->after('min_quantity');

            // FK to units table — nullable so old products without a unit still work
            $table->foreignId('unit_id')->nullable()->after('quantity_step')
                  ->constrained('units')->nullOnDelete();
        });

        // Point all existing products to the default "piece" unit (id = 1)
        DB::table('products')->update(['unit_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['min_quantity', 'quantity_step', 'unit_id']);
            $table->smallInteger('quantity')->default(0)->change();
        });
    }
};
