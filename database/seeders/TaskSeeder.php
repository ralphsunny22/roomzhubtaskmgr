<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tasks')->insert([
            [
                'created_by' => 1,
                'freelancer_id' => null,
                'task_title' => 'Fix plumbing issues',
                'task_date_preceed' => 'on', // on, before, flexible
                'task_date' => '2024-09-28',
                'task_part_of_day' => 'morning', // morning, mid-day, afternoon, evening
                'task_time_of_day' => 'before 10am', // before 10am, 10am - 2pm, 2pm - 6pm, after 6pm
                'is_removal_task' => false, // involves transit

                'pickup_latitude' => null,
                'pickup_longitude' => null,
                'pickup_city' => null,
                'pickup_state' => null,
                'pickup_country' => null,
                'pickup_address' => null,

                'dropoff_latitude' => null,
                'dropoff_longitude' => null,
                'dropoff_city' => null,
                'dropoff_state' => null,
                'dropoff_country' => null,
                'dropoff_address' => null,

                'is_done_online' => false,
                'is_done_inperson' => true,
                'task_description' => 'Fix the broken sink and check the pipes in the kitchen.',
                'task_images' => json_encode(['noimage.png', 'noimage.png']),
                'task_budget' => 150.50,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'created_by' => 1,
                'freelancer_id' => null,
                'task_title' => 'Replace broken tiles',
                'task_date_preceed' => 'on', // on, before, flexible
                'task_date' => '2024-09-28',
                'task_part_of_day' => 'mid-day', // morning, mid-day, afternoon, evening
                'task_time_of_day' => '10am - 2pm', // before 10am, 10am - 2pm, 2pm - 6pm, after 6pm
                'is_removal_task' => false, // involves transit

                'pickup_latitude' => '40.712776',
                'pickup_longitude' => '-74.005974',
                'pickup_city' => 'New York',
                'pickup_state' => 'NY',
                'pickup_country' => 'USA',
                'pickup_address' => '1234 Main St, New York, NY',

                'dropoff_latitude' => '40.730610',
                'dropoff_longitude' => '-73.935242',
                'dropoff_city' => 'Brooklyn',
                'dropoff_state' => 'NY',
                'dropoff_country' => 'USA',
                'dropoff_address' => '5678 Elm St, Brooklyn, NY',

                'is_done_online' => false,
                'is_done_inperson' => true,
                'task_description' => 'Fix the broken sink and check the pipes in the kitchen.',
                'task_images' => json_encode(['noimage.png', 'noimage.png']),
                'task_budget' => 150.50,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'created_by' => 3,
                'freelancer_id' => null,
                'task_title' => 'Replace broken tiles',
                'task_date_preceed' => 'on', // on, before, flexible
                'task_date' => '2024-09-28',
                'task_part_of_day' => 'mid-day', // morning, mid-day, afternoon, evening
                'task_time_of_day' => '10am - 2pm', // before 10am, 10am - 2pm, 2pm - 6pm, after 6pm
                'is_removal_task' => false, // involves transit

                'pickup_latitude' => '40.712776',
                'pickup_longitude' => '-74.005974',
                'pickup_city' => 'New York',
                'pickup_state' => 'NY',
                'pickup_country' => 'USA',
                'pickup_address' => '1234 Main St, New York, NY',

                'dropoff_latitude' => '40.730610',
                'dropoff_longitude' => '-73.935242',
                'dropoff_city' => 'Brooklyn',
                'dropoff_state' => 'NY',
                'dropoff_country' => 'USA',
                'dropoff_address' => '5678 Elm St, Brooklyn, NY',

                'is_done_online' => false,
                'is_done_inperson' => true,
                'task_description' => 'Arrange my table sets',
                'task_images' => json_encode(['noimage.png', 'noimage.png']),
                'task_budget' => 150.50,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
