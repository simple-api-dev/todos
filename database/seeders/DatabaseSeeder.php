<?php

namespace Database\Seeders;

use App\Models\Integration;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(20)->create();
        Integration::factory()->count(5)->create();
        Todo::factory()->count(200)->create();
    }
}
