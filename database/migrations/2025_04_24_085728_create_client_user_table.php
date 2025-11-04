<?php

use App\Models\Client\Client;
use App\Enums\Client\ClientStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnUpdate();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('status')->default(ClientStatus::INACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_user');
    }
};
