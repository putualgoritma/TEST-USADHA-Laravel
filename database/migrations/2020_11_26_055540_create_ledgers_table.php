<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('customers_id');
            //$table->unsignedBigInteger('users_id');
            $table->date('register');
            $table->char('title', 50);
            $table->text('memo');
            $table->timestamps();            
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('ledgers');
            $table->foreign('customers_id')->references('id')->on('customers');
            //$table->foreign('users_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledgers');
    }
}
