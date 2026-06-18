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
        Schema::create('brands', function (Blueprint $collection) {
            $collection->id();
            $collection->string('brand_name');
            $collection->string('brand_location')->nullable();
            $collection->string('brand_detail')->nullable();
            $collection->string('brand_pic')->nullable();
            $collection->string('brand_pic_public_id')->nullable();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
