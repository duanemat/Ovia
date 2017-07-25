<?php

use Illuminate\Database\Seeder;

class RoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rooms = [
            [
              "name"=>"Room 1",
              "guests"=>2,
                "storage"=>1,
                "created_at"=>time(),
                "updated_at"=>time()

            ],
            [
                "name"=>"Room 2",
                "guests"=>2,
                "storage"=>0,
                "created_at"=>time(),
                "updated_at"=>time()
            ],
            [
                "name"=>"Room 3",
                "guests"=>1,
                "storage"=>2,
                "created_at"=>time(),
                "updated_at"=>time()
            ],
            [
                "name"=>"Room 4",
                "guests"=>1,
                "storage"=>0,
                "created_at"=>time(),
                "updated_at"=>time()
            ],
        ];
        DB::table('Room')->insert($rooms);
    }
}
