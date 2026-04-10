# Automatic Inactivity-Based Archiving System

## Overview

This system automatically archives employee accounts that have been inactive for 2+ years, following your professor's recommendation for better account management.

## How It Works

### 1. Login Tracking
- Every time a user logs in, their `last_login_at` timestamp is updated
- This is tracked in the `AuthenticatedSessionController@store()` method

### 2. Automatic Inactivity Check
- A custom Artisan command `users:check-inactive` runs automatically
- Finds users who haven't logged in for 2+ years
- Automatically changes their status to "Inactive"
- Automatically archives them (moves to archive page)
- Logs all actions for audit purposes

### 3. Real-Time Updates
- Uses existing real-time event system
- Users disappear from manage-user page immediately
- Users appear in archive page immediately
- No page refresh needed

## Setup Instructions

### Step 1: Database Migration (Already Done)
```bash
php artisan migrate
```
This adds the `last_login_at` column to the users table.

### Step 2: Set Up Cron Job (Required for Automation)

Add this cron job to your server to run daily at 2 AM:

```bash
# Edit crontab
crontab -e

# Add this line (adjust path to your project)
0 2 * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 3: Alternative Manual Execution

You can also run the command manually:

```bash
# Test without making changes (dry run)
php artisan users:check-inactive --dry-run

# Run for real
php artisan users:check-inactive
```

## Command Details

### `users:check-inactive` Command

**Options:**
- `--dry-run` : Shows what would be done without making changes

**What it does:**
1. Finds users where `last_login_at` is older than 2 years OR `last_login_at` is null
2. Only processes users with `status = 'Active'` and `is_archive = false`
3. Updates found users:
   - `status = 'Inactive'`
   - `is_archive = true`
   - `archived_at = now()`
   - `archived_by = 'System (2 Years Inactivity)'`
4. Logs all actions to Laravel log file

**Schedule:**
- Runs daily at 2:00 AM (configurable in `app/Console/Kernel.php`)
- Can be changed to weekly, monthly, etc.

## Security & Compliance Benefits

### 🔒 Security
- Automatically removes dormant accounts
- Reduces attack surface from inactive accounts
- Follows security best practices

### 📋 Compliance
- Meets professor's recommendation
- Automated audit trail
- Proper account lifecycle management

### 🔄 Consistency
- Uses existing archive system
- Real-time UI updates
- No manual intervention needed

## Monitoring & Logs

### Log Entries
All automatic archiving actions are logged:
```
[2024-04-04 02:00:00] local.INFO: User automatically archived due to inactivity 
{
    "user_id": 123,
    "user_email": "user@example.com", 
    "last_login_at": "2022-03-15 14:30:00",
    "archived_at": "2024-04-04 02:00:00",
    "reason": "2 years inactivity"
}
```

### Manual Monitoring
```bash
# Check who would be archived
php artisan users:check-inactive --dry-run

# Check Laravel logs
tail -f storage/logs/laravel.log | grep "automatically archived"
```

## Customization

### Change Inactivity Period
Edit `app/Console/Commands/CheckInactiveUsers.php`:
```php
// Change from 2 years to 1 year
$oneYearAgo = Carbon::now()->subYear();
```

### Change Schedule
Edit `app/Console/Kernel.php`:
```php
// Run weekly instead of daily
$schedule->command('users:check-inactive')
    ->weekly()
    ->sundays()
    ->at('03:00');
```

### Change Archive Reason
Edit the command to customize the `archived_by` message.

## Testing

### Test with Old Accounts
```bash
# Manually set an old login date for testing
php artisan tinker
$user = User::find(1);
$user->update(['last_login_at' => now()->subYears(3)]);

# Run the command
php artisan users:check-inactive --dry-run
```

### Test Real-Time Updates
1. Set up a test account with old login date
2. Run the command
3. Watch the user disappear from manage-user page
4. Watch the user appear in archive page
5. No page refresh needed!

## Complete Workflow

1. **User logs in** → `last_login_at` updated
2. **2+ years pass** → User becomes inactive
3. **Daily cron runs** → Command finds inactive users
4. **User archived** → Status=Inactive + Archive=True
5. **Real-time update** → User moves to archive page
6. **Admin sees** → All inactive accounts in archive page

This system provides complete automated account lifecycle management following security best practices and your professor's recommendations! 🎉
