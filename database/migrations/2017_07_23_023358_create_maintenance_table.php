<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaintenanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Maintenance', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('time_clean_per_guest');
            $table->integer('time_clean_per_room');
            $table->float('max_hours_day', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Maintenance');
    }
}
