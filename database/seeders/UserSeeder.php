<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample data for the seekers table nick@email.com, client@email.com, freelancer@email.com
        $user = new User();
        $user->name = 'Raphael Santos';
        $user->email = 'santosralph2022@gmail.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;

        $user->save();

        $user = new User();
        $user->name = 'Ugo Raphael';
        $user->email = 'ralphsunny114@gmail.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;
        $user->save();

        $user = new User();
        $user->name = 'Nick Dev';
        $user->email = 'nick@email.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;
        $user->save();

        $user = new User();
        $user->name = 'Dreymi';
        $user->email = 'dr3ymi@email.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;
        $user->save();

        $user = new User();
        $user->name = 'Test Freelancer';
        $user->email = 'freelancer@email.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;
        $user->save();

        $user = new User();
        $user->name = 'Test Client';
        $user->email = 'client@email.com';
        $user->password = Hash::make('password');
        $user->is_phone_number_visible = false;
        $user->save();



    }
}
