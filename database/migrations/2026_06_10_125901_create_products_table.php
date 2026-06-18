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
        Schema::create('products', function (Blueprint $collection) {
            $collection->id();
            $collection->string("product_name");
            $collection->decimal("product_price",10,2);
            $collection->decimal("product_discount",10,2);
            $collection->string("product_status");
            $collection->string("product_manufactured_date");
            $collection->string("product_expired_date");
            $collection->string("product_detail")->nullable();
            $collection->string("product_pic")->nullable();
            $collection->string("product_pic_public_id")->nullable();
            $collection->objectId("category_id")->nullable();
            $collection->objectId("brand_id")->nullable();
            $collection->timestamps();
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
