<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Reservation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('room_id');
            $table->foreign('room_id')->references('id')->on('Room');
            $table->integer('guests_count');
            $table->integer('storage_count');
            $table->date('stay_date_start');
            $table->date('stay_date_end');
            $table->float('check_in_time', 4, 2);
            $table->integer('guest_id')->unsigned();
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
        Schema::dropIfExists('Reservation');
    }
}
