<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserAttendance;
use Illuminate\Support\Facades\DB;

class CloseStaleAttendance extends Command
{
    protected $signature = 'attendance:close-stale {--minutes=10}';
    protected $description = 'Close open attendances with no heartbeat for N minutes';

    public function handle(): int
    {
        $cutoff = now()->subMinutes((int)$this->option('minutes'));

        $count = UserAttendance::whereNull('check_out')
            ->where('status', 'in')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_heartbeat_at')
                    ->orWhere('last_heartbeat_at', '<', $cutoff);
            })
            ->update([
                'check_out'         => now(),
                'hours_worked'      => DB::raw('ROUND(TIMESTAMPDIFF(MINUTE, check_in, UTC_TIMESTAMP())/60, 2)'),
                'last_heartbeat_at' => now(),
                'status'            => 'out',
                'notes'             => DB::raw(
                    "TRIM(CONCAT(COALESCE(notes,''), CASE WHEN COALESCE(notes,'') <> '' THEN '\n' ELSE '' END, '[Auto-checkout: stale session]'))"
                ),
            ]);

        $this->info("Closed {$count} stale attendances.");
        return self::SUCCESS;
    }
}
