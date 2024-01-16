<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationType::create([
            'name' => 'Alert', 
            'icon' => 'ri-alarm-warning-line' , 
            'color' => 'danger'
        ]);
        NotificationType::create([
            'name' => 'Message', 
            'icon' => 'ri-chat-smile-2-line' , 
            'color' => 'success'
        ]);
    }
}
