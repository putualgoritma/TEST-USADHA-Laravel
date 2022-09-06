<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['D', 'C']);
            $table->enum('status', ['onhold', 'onhand', 'closed']);            
            $table->unsignedInteger('orders_id')->default(0);
            $table->unsignedInteger('products_id')->default(0);
            $table->unsignedBigInteger('warehouses_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orders_id')->references('id')->on('orders');
            $table->foreign('products_id')->references('id')->on('products');
            $table->foreign('warehouses_id')->references('id')->on('warehouses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_order_details');
    }
}
