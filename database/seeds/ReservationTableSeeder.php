<?php

use Illuminate\Database\Seeder;

class ReservationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Just seed a couple Reservations for testing
        /*
         *  $table->increments('id');
            $table->integer('room_id');
            $table->foreign('room_id')->references('id')->on('Room');
            $table->date('stay_date_start');
            $table->date('stay_date_end');
            $table->float('check_in_time', 4, 2);
            $table->integer('guest_id')->unsigned();
            $table->timestamps();
         */
        DB::table("Reservation")->insert([
            'room_id'=>"2",
            'stay_date_start'=>(new DateTime())->format('Y-m-d'),
            'stay_date_end'=>(new DateTime())->modify('+1 day')->format('Y-m-d'),
            'check_in_time'=>12,
            'guest_id'=>123,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>(new DateTime())->modify('+1 day')->format('Y-m-d'),
            'stay_date_end'=>(new DateTime())->modify('+3 day')->format('Y-m-d'),
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
    }
}
