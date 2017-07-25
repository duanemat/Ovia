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
        $stay_date = (new DateTime());
        // 1 Day stay
        DB::table("Reservation")->insert([
            'room_id'=>"2",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>2,
            'storage_count'=>0,
            'check_in_time'=>12,
            'guest_id'=>123,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        // 2 Day stay
        DB::table("Reservation")->insert([
            'room_id'=>"3",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>1,
            'check_in_time'=>12,
            'guest_id'=>321,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        // 2 Day stay
        DB::table("Reservation")->insert([
            'room_id'=>"3",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>1,
            'check_in_time'=>12,
            'guest_id'=>321,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        // 3 Day Stay, starting tomorrow
        $stay_date = (new DateTime())->modify('+1 day');
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>0,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>0,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>0,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        // 3 Day Stay, a week later
        $stay_date = (new DateTime())->modify('+7 day');
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>1,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>1,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
        DB::table("Reservation")->insert([
            'room_id'=>"1",
            'stay_date_start'=>$stay_date->format('Y-m-d'),
            'stay_date_end'=>$stay_date->modify('+1 day')->format('Y-m-d'),
            'guests_count'=>1,
            'storage_count'=>1,
            'check_in_time'=>12,
            'guest_id'=>456,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
    }
}
