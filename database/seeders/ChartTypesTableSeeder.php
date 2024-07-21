<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('chat_types')->insert([
            [
                'id' => 1,
                'name' => 'Incoming',
                'icon' => 'ri-chat-check-line',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 2,
                'name' => 'Outgoing',
                'icon' => 'ri-chat-delete-line',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
        ]);
    }
}
