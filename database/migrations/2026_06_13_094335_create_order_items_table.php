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
        Schema::create('order_items', function (Blueprint $collection) {
            $collection->id();
            $collection->objectId("order_id")->nullable();
            $collection->objectId("product_id")->nullable();
            $collection->numeric("qty");
            $collection->decimal("price",10,2);
            $collection->decimal("discount",10,2);
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
