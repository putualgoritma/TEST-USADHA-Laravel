<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {              
            $table->enum('type', ['topup', 'transfer', 'withdraw', 'production', 'sale', 'buy', 'sale_retur', 'buy_retur', 'stock_trsf']);
            $table->enum('status', ['pending', 'approved', 'closed']);
            $table->unsignedBigInteger('customers_id')->default(0);
            $table->unsignedBigInteger('ledgers_id')->default(0);

            $table->foreign('customers_id')->references('id')->on('customers');
            $table->foreign('ledgers_id')->references('id')->on('ledgers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
