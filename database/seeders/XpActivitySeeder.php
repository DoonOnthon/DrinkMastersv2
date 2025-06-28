<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class XpActivitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            foreach (range(0, 6) as $i) {
                $date = Carbon::now()->subDays($i);
                Activity::create([
                    'user_id' => $user->id,
                    'description' => 'Gained XP',
                    'type' => 'user', // or 'system'
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // Increment user's XP to match the chart
                $user->increment('xp', rand(50, 150));
            }
        }
    }
}
