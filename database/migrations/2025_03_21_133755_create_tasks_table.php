<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->string('product_id', 6);
            $table->enum('product_category', ['clothing', 'ornaments', 'other']);
            $table->enum('buyer_gender', ['male', 'female']);
            $table->integer('buyer_age');
            $table->text('order_location');
            $table->boolean('international_shipping')->default(false);
            $table->decimal('sales_price', 10, 2);
            $table->decimal('shipping_charges', 10, 2)->nullable(); // if international_shipping = true
            $table->decimal('sales_per_unit', 10, 2); // computed manually
            $table->integer('quantity');
            $table->decimal('total_sales', 12, 2); // computed manually
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
