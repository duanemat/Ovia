<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomCleaningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Room_Cleaning', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('room_id');
            $table->foreign('room_id')->references('id')->on('Room');
            $table->integer('maintenance_team_id');
            $table->foreign('maintenance_team_id')->references('id')->on('Maintenance');
            $table->date('date_cleaned');
            $table->float('start_time', 4, 2);
            $table->float('length_of_cleaning');
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
        Schema::dropIfExists('Room_Cleaning');
    }
}
