<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OpportunityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Opportunity::create([
            'name' => 'Invest in Real Estate',
            'description' => 'An excellent real estate investment opportunity.',
            'value_id' => '1',
            'date' => '2023-10-01',
            'user_id' => '2',
            'category_id' => '1',
            'sub_category_id' => '1',
            'type_id' => '5',
            'status_id' => '1'
        ]);

        Opportunity::create([
            'name' => 'Tech Start-up Funding',
            'description' => 'Seeking investment for a new tech start-up.',
            'value_id' => '2',
            'date' => '2023-11-15',
            'user_id' => '1',
            'category_id' => '4',
            'sub_category_id' => '3',
            'type_id' => '2',
            'status_id' => '1'
        ]);

        Opportunity::create([
            'name' => 'Acquire a Manufacturing Company',
            'description' => 'Opportunity to acquire a thriving manufacturing business.',
            'value_id' => '4',
            'date' => '2023-12-31',
            'user_id' => '2',
            'category_id' => '1',
            'sub_category_id' => '3',
            'type_id' => '3',
            'status_id' => '1'
        ]);

        Opportunity::create([
            'name' => 'Franchise Outlet Sale',
            'description' => 'A popular food franchise outlet is up for sale.',
            'value_id' => '1',
            'date' => '2023-12-01',
            'user_id' => '3',
            'category_id' => '2',
            'sub_category_id' => '2',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'Renewable Energy Investment',
            'description' => 'Invest in a renewable energy startup with excellent growth prospects.',
            'value_id' => '2',
            'date' => '2023-10-20',
            'user_id' => '1',
            'category_id' => '2',
            'sub_category_id' => '2',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'Software Company Acquisition',
            'description' => 'A software company with stable revenue is available for acquisition.',
            'value_id' => '3',
            'date' => '2023-11-10',
            'user_id' => '3',
            'category_id' => '3',
            'sub_category_id' => '3',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'Healthcare Expansion',
            'description' => 'A healthcare company is seeking investment for expansion.',
            'value_id' => '1',
            'date' => '2023-09-30',
            'user_id' => '3',
            'category_id' => '3',
            'sub_category_id' => '2',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'E-commerce Venture',
            'description' => 'Looking to invest in a rapidly growing e-commerce business.',
            'value_id' => '3',
            'date' => '2023-12-25',
            'user_id' => '2',
            'category_id' => '3',
            'sub_category_id' => '2',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'Agricultural Project',
            'description' => 'Investment needed for an agricultural project with high returns.',
            'value_id' => '1',
            'date' => '2023-08-15',
            'user_id' => '1',
            'category_id' => '4',
            'sub_category_id' => '2',
            'type_id' => '5',
            'status_id' => '1'
        ]);
        
        Opportunity::create([
            'name' => 'Automotive Manufacturing Unit',
            'description' => 'Automotive parts manufacturing unit up for sale.',
            'value_id' => '4',
            'date' => '2024-01-01',
            'user_id' => '1',
            'category_id' => '2',
            'sub_category_id' => '2',
            'type_id' => '4',
            'status_id' => '1'
        ]);
    }
}
