<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->integer('transaction_id')->unique();
        $table->string('customer_name');
        $table->string('customer_address');
        $table->string('customer_contact');
        $table->string('delivery_status')->default('Pending');
        $table->string('proof_of_delivery')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
