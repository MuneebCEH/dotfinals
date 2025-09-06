<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserAttendance;
use Illuminate\Support\Carbon;

class AutoCheckoutInactiveUsers extends Command
{
    protected $signature = 'attendance:auto-checkout {--grace=5}'; // minutes
    protected $description = 'Auto-checkout users with stale heartbeats';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes((int)$this->option('grace'));

        $rows = UserAttendance::whereNull('check_out')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_heartbeat_at')
                    ->orWhere('last_heartbeat_at', '<', $cutoff);
            })
            ->get();

        foreach ($rows as $att) {
            $att->check_out = $att->last_heartbeat_at ?: Carbon::now();
            if ($att->check_in) {
                $att->hours_worked = $att->check_in->diffInMinutes($att->check_out) / 60;
            }
            $att->status = 'out';
            $att->save();
        }

        $this->info("Auto-checked out {$rows->count()} user(s).");
        return self::SUCCESS;
    }
}
