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
        Schema::create('admins', function (Blueprint $collection) {
            $collection->id();
            $collection->string('username')->unique();
            $collection->string('password');
            $collection->string('admin_pic')->nullable();
            $collection->string('admin_pic_public_id')->nullable();
            $collection->enum('role',['super_admin','manager','pharmacist'])->default('pharmacist');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
