<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('sales_price', 'base_price');
            $table->renameColumn('shipping_charges', 'shipping_fee');
            $table->renameColumn('sales_per_unit', 'unit_cost');
            $table->renameColumn('total_sales', 'final_amount');

            $table->string('status')->default('pending')->after('total_sales');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('base_price', 'sales_price');
            $table->renameColumn('shipping_fee', 'shipping_charges');
            $table->renameColumn('unit_cost', 'sales_per_unit');
            $table->renameColumn('final_amount', 'total_sales');

            $table->dropColumn('status');
        });
    }
};
