<?php

use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use Illuminate\Support\Facades\Schema;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignIdFor(Client::class)->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('client_phone_id')->nullable()->constrained('client_phones')->nullOnDelete();
            $table->foreignId('client_email_id')->nullable()->constrained('client_emails')->nullOnDelete();
            $table->foreignId('client_address_id')->nullable()->constrained('client_addresses')->nullOnDelete();
            $table->tinyInteger('status')->default(OrderStatus::DRAFT->value);
            $table->decimal('discount',8,2);
            $table->decimal('total_cost',8,2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->tinyInteger('discount_type')->default(DiscountType::NO_DISCOUNT->value);
            $table->decimal('price_after_discount', 10, 2)->default(0);
            // $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
