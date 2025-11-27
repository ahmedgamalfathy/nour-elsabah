<?php

use App\Enums\Product\LimitedQuantity;
use App\Enums\Product\ProductStatus;
use App\Enums\Product\UnitType;
use App\Models\Product\Category;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */
    public function up(): void
    {//name ,description, price, status
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('price',15,2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->boolean('is_limited_quantity')->default(LimitedQuantity::UNLIMITED->value);
            $table->smallInteger('quantity')->default(0);
            $table->tinyInteger('status')->default(ProductStatus::INACTIVE->value);
            //crossed_price, is_promotion, is_free_shipping, unit_type
            $table->decimal('crossed_price',15,2)->default(0);
            $table->boolean('is_promotion')->default(false);
            $table->boolean('is_free_shipping')->default(false);
            $table->tinyInteger('unit_type')->default(UnitType::UNIT->value);
            $table->foreignIdFor(Category::class,'category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(Category::class,'sub_category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
