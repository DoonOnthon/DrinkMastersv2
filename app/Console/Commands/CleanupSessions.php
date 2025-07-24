<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlaySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old and completed play sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $cleanedCount = 0;

        // 1. Clean up completed sessions (1 hour after completion)
        $completedSessions = PlaySession::where('completed_at', '<=', $now->subHour())
            ->where('auto_cleanup', true)
            ->get();

        foreach ($completedSessions as $session) {
            $this->info("Cleaning up completed session: {$session->code}");
            $session->delete();
            $cleanedCount++;
        }

        // 2. Clean up inactive sessions (3 hours of inactivity)
        $inactiveSessions = PlaySession::where('last_activity', '<=', $now->subHours(2))
            ->whereNull('completed_at')
            ->where('auto_cleanup', true)
            ->get();

        foreach ($inactiveSessions as $session) {
            $this->info("Cleaning up inactive session: {$session->code}");
            $session->delete();
            $cleanedCount++;
        }

        // 3. Clean up sessions older than 24 hours regardless of status
        $oldSessions = PlaySession::where('created_at', '<=', $now->subHours(24))
            ->where('auto_cleanup', true)
            ->get();

        foreach ($oldSessions as $session) {
            $this->info("Cleaning up old session: {$session->code}");
            $session->delete();
            $cleanedCount++;
        }

        $this->info("Cleaned up {$cleanedCount} sessions.");
        Log::info("Session cleanup completed. Removed {$cleanedCount} sessions.");

        return 0;
    }
}
