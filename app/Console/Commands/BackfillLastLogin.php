<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class BackfillLastLogin extends Command
{
    protected $signature = 'users:backfill-last-login';
    protected $description = 'Assign random last_login_at timestamps to users';

    public function handle()
    {
        $this->info("Backfilling last_login_at for users...");

        $count = 0;

        User::whereNull('last_login_at')->get()->each(function ($user) use (&$count) {
            $user->last_login_at = now()->subDays(rand(0, 60))->subMinutes(rand(0, 1440));
            $user->save();
            $count++;
        });

        $this->info("Updated {$count} users.");
    }
}

