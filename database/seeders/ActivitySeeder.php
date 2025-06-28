<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        Activity::factory()->count(100)->create();
    }
}
// This seeder creates 100 random activity records for users in the database.
