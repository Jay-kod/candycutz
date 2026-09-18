<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('creates a database backup snapshot via db:backup command', function () {
    $filename = 'automated_test_backup_'.uniqid().'.sqlite';
    $exitCode = Artisan::call('db:backup', [
        '--filename' => $filename,
    ]);

    expect($exitCode)->toBe(0);

    $backupPath = storage_path('app/backups/'.$filename);
    expect(file_exists($backupPath))->toBeTrue();
    expect(filesize($backupPath))->toBeGreaterThan(0);

    // Cleanup
    if (file_exists($backupPath)) {
        unlink($backupPath);
    }
});

it('restores database and recovers mutated state during restore drill', function () {
    // 1. Create a unique canary user record
    $canaryEmail = 'canary_'.uniqid().'@candycutz.com';
    $user = User::factory()->create([
        'name' => 'Canary Drill User',
        'email' => $canaryEmail,
    ]);

    expect(User::where('email', $canaryEmail)->exists())->toBeTrue();

    // 2. Execute backup
    $filename = 'automated_drill_'.uniqid().'.sqlite';
    $backupPath = storage_path('app/backups/'.$filename);

    $backupExit = Artisan::call('db:backup', [
        '--filename' => $filename,
    ]);
    expect($backupExit)->toBe(0);
    expect(file_exists($backupPath))->toBeTrue();

    // 3. Mutate database (delete the canary user)
    User::where('email', $canaryEmail)->delete();
    expect(User::where('email', $canaryEmail)->exists())->toBeFalse();

    // 4. Perform restore drill
    $restoreExit = Artisan::call('db:restore', [
        'file' => $backupPath,
        '--force' => true,
    ]);
    expect($restoreExit)->toBe(0);

    // 5. Verify the canary record is completely recovered
    expect(User::where('email', $canaryEmail)->exists())->toBeTrue();

    // Cleanup
    if (file_exists($backupPath)) {
        unlink($backupPath);
    }
});
