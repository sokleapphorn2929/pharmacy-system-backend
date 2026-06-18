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
        Schema::create('payments', function (Blueprint $collection) {
            $collection->id();
            $collection->objectId("order_id")->nullable();
            $collection->objectId("user_id")->nullable();
            $collection->decimal("total_price");
            $collection->decimal("total_discount");
            $collection->decimal("tax");
            $collection->string("payment_method");
            $collection->string("payment_status");
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
