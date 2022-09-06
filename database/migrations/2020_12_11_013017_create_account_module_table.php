<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_module', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounts_id')->default(0);
            $table->unsignedBigInteger('modules_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('accounts_id')->references('id')->on('accounts');
            $table->foreign('modules_id')->references('id')->on('modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_module');
    }
}
