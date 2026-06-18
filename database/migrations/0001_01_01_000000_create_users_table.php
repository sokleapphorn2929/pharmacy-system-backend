<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $collection) {
            $collection->id();
            $collection->string('username');
            $collection->string('email')->unique();
            $collection->string('phone')->unique();
            $collection->text('address')->nullable();
            $collection->string('profile_pic')->nullable();
            $collection->string('profile_pic_public_id')->nullable();
            $collection->timestamp('email_verified_at')->nullable();
            $collection->string('password');
            $collection->string('verification_code')->nullable();
            $collection->timestamp('code_expires_at')->nullable();
            $collection->rememberToken();
            $collection->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $collection) {
            $collection->string('email')->primary();
            $collection->string('token');
            $collection->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $collection) {
            // $collection->string('id')->primary();
            $collection->string('id');
            $collection->foreignId('user_id')->nullable()->index();
            $collection->string('ip_address', 45)->nullable();
            $collection->text('user_agent')->nullable();
            $collection->longText('payload');
            $collection->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
