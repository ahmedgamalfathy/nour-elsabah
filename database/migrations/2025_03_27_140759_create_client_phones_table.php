<?php

use App\Enums\IsMain;
use App\Models\Client\Client;
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
        //client_id , phone , is_main , country_code
        //client_id , address, is_main
        Schema::create('client_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('phone');//secondary
            $table->boolean('is_main')->default(IsMain::SECONDARY->value);
            $table->string('country_code')->nullable();
            $table->softDeletes();
            // $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
