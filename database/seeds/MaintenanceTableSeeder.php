<?php

use Illuminate\Database\Seeder;

class MaintenanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("Maintenance")->insert([
           'name'=>"Cleaning Team 1",
           'time_clean_per_guest'=>30,
            'time_clean_per_room'=>60,
            'max_hours_day'=>8,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
    }
}
