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
        Schema::create('invoices', function (Blueprint $collection) {
            $collection->id();
            $collection->objectId("payment_id")->nullable();
            $collection->objectId("admin_id")->nullable();
            $collection->string("invoice_number")->unique();
            $collection->date("invoice_create_at");
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
