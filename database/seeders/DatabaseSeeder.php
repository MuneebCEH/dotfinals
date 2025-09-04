<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create one default admin account
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_admin' => true
            ]
        );

        // Create 60 agents
        $agents = [];
        for ($i = 1; $i <= 60; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT); // 001, 002, ..., 060
            $email = "Agent{$number}@reincarnationcloud.com";
            $name = "Agent{$number}";

            $agent = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_admin' => false
                ]
            );

            $agents[] = $agent;
        }

        // Create attendance records for 30 online agents
        $this->createAttendanceForOnlineAgents($agents);
    }

    /**
     * Create attendance records for 30 random agents as online
     */
    private function createAttendanceForOnlineAgents($agents)
    {
        // Shuffle agents and take 30 random ones
        $onlineAgents = collect($agents)->shuffle()->take(30);

        $today = Carbon::today();

        foreach ($onlineAgents as $agent) {
            // Check if the agent already has an attendance record for today
            $existingAttendance = UserAttendance::where('user_id', $agent->id)
                ->whereDate('check_in', $today)
                ->first();

            if (!$existingAttendance) {
                // Create check-in record for today (random time between 8 AM and 11 AM)
                $checkInTime = $today->copy()
                    ->addHours(8)
                    ->addMinutes(rand(0, 180)); // Random minutes between 0-180 (3 hours)

                UserAttendance::create([
                    'user_id' => $agent->id,
                    'check_in' => $checkInTime,
                    'notes' => 'Auto-generated check-in for seeding',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->info("Created check-in for {$agent->name} at {$checkInTime->format('g:i A')}");
            } else {
                $this->command->info("{$agent->name} already has attendance record for today");
            }
        }

        $this->command->info("Created attendance records for 30 online agents");
    }
}
