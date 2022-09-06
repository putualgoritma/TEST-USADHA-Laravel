<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ledgers_id');
            $table->unsignedBigInteger('accounts_id');
            $table->enum('entry_type', ['D', 'C'])->default('D');
            $table->decimal('amount', 20, 2);
            $table->timestamps();            
            $table->softDeletes();

            $table->foreign('ledgers_id')->references('id')->on('ledgers');
            $table->foreign('accounts_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger_entries');
    }
}
