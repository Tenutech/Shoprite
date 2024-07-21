<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BanksTableSeeder::class,
            BrandsTableSeeder::class,
            ChatCategoriesTableSeeder::class,
            ChecksTableSeeder::class,
            DisabilitiesTableSeeder::class,
            DurationsTableSeeder::class,
            EducationsTableSeeder::class,
            NotificationTypeTableSeeder::class,
            ProvincesTableSeeder::class,
            RacesTableSeeder::class,
            ReasonsTableSeeder::class,
            RetrenchmentsTableSeeder::class,
            RolesTableSeeder::class,
            ScoreWeightingsTableSeeder::class,
            SettingsTableSeeder::class,
            StatusTableSeeder::class,
            TagsTableSeeder::class,
            TransportsTableSeeder::class,
            TypesTableSeeder::class,
        ]);
    }
}
