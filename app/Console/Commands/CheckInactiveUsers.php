<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-inactive {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark users as inactive after 5 years of no login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fiveYearsAgo = Carbon::now()->subYears(5);
        
        $this->info('Checking for users inactive for 5+ years...');
        $this->info("Cutoff date: {$fiveYearsAgo->format('Y-m-d H:i:s')}");
        
        // Find users who haven't logged in for 5+ years and are still Active
        $inactiveUsers = User::where('last_login_at', '<', $fiveYearsAgo)
            ->orWhereNull('last_login_at') // Handle users who never logged in
            ->where('status', 'Active')
            ->where('is_archive', false)
            ->get();
            
        $count = $inactiveUsers->count();
        $this->info("Found {$count} users inactive for 5+ years");
        
        if ($count === 0) {
            $this->info('No inactive users found. All good!');
            return 0;
        }
        
        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made:');
            foreach ($inactiveUsers as $user) {
                $lastLogin = $user->last_login_at ? 
    (is_string($user->last_login_at) ? 
        Carbon::parse($user->last_login_at)->format('Y-m-d') : 
        $user->last_login_at->format('Y-m-d')
    ) : 'Never';
                $this->line("  - {$user->name} ({$user->email}) - Last login: {$lastLogin}");
            }
            return 0;
        }
        
        // Process each inactive user
        $processed = 0;
        foreach ($inactiveUsers as $user) {
            $lastLogin = $user->last_login_at ? 
    (is_string($user->last_login_at) ? 
        Carbon::parse($user->last_login_at)->format('Y-m-d') : 
        $user->last_login_at->format('Y-m-d')
    ) : 'Never';
            
            $this->info("Processing: {$user->name} ({$user->email}) - Last login: {$lastLogin}");
            
            // Update user to inactive and archive them
            $user->update([
                'status' => 'Inactive',
                'is_archive' => true,
                'archived_at' => now(),
                'archived_by' => 'System (5 Years Inactivity)',
            ]);
            
            // Log the action
            Log::info('User automatically archived due to inactivity', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'last_login_at' => $user->last_login_at,
                'archived_at' => now(),
                'reason' => '5 years inactivity'
            ]);
            
            $processed++;
            $this->line("  ✓ Marked as Inactive and Archived");
        }
        
        $this->info("Successfully processed {$processed} users");
        $this->info('All inactive users have been marked as Inactive and moved to archive.');
        
        return 0;
    }
}
