<?php


use App\Enums\Client\ClientType;
use App\Enums\Client\ClientStatus;
use Illuminate\Support\Facades\Schema;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
              $table->integer('points')->default(0);
            $table->string('name')->nullable();
            $table->tinyInteger('type')->default(ClientType::VISITOR->value);
            $table->text('note')->nullable();
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
        Schema::dropIfExists('clients');
    }
};
