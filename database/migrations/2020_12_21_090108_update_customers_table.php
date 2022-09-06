<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {              
            // $table->unsignedBigInteger('parent_id')->default(0)->change();
            // $table->unsignedBigInteger('ref_id')->default(0)->change();
            $table->dropForeign('customers_parent_id_foreign');
            $table->dropForeign('customers_ref_id_foreign');            
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
