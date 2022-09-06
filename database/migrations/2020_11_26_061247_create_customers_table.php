<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('ref_id');
            $table->date('register');
            $table->char('code', 10);
            $table->char('password', 10);
            $table->char('name', 100);
            $table->char('last_name', 100);
            $table->char('phone', 50);
            $table->char('phone2', 50);
            $table->char('email', 50);
            $table->string('address');
            $table->string('address2');
            $table->enum('type', ['general', 'agent', 'member'])->default('general');
            $table->enum('status', ['active', 'pending', 'closed'])->default('active');
            $table->timestamps();            
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('customers');
            $table->foreign('ref_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
