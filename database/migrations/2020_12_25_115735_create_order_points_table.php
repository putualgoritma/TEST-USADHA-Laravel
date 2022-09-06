<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['D', 'C']);
            $table->enum('status', ['onhold', 'onhand', 'closed']);            
            $table->unsignedInteger('orders_id')->default(0);
            $table->unsignedBigInteger('customers_id')->default(0);
            $table->unsignedBigInteger('points_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orders_id')->references('id')->on('orders');
            $table->foreign('customers_id')->references('id')->on('customers');
            $table->foreign('points_id')->references('id')->on('points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_points');
    }
}
