<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyPhoneVertifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {



        Schema::table('phone_vertifies', function(Blueprint $table) {
            $table->dropColumn(['id']);
        });

        Schema::table('phone_vertifies', function(Blueprint $table) {
            $role = DB::table('phone_vertifies')->delete();
            $table->uuid('id')->primary()->unique();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {


    }

}
