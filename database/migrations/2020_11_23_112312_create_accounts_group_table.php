<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('code', 10);
            $table->string('name');
            $table->unsignedBigInteger('accounts_type_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('accounts_type_id')->references('id')->on('accounts_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts_group');
    }
}
