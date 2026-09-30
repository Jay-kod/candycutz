<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $backupDir = storage_path('app/backups');
    if (! File::isDirectory($backupDir)) {
        File::makeDirectory($backupDir, 0755, true);
    }
});

it('allows admin to fetch system overview and health details', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/system/overview');
    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure([
        'data' => [
            'status',
            'summary' => [
                'database_connected',
                'open_groups_count',
                'events_24h_count',
                'backups_count',
            ],
            'server_time',
        ],
    ]);

    $detailResponse = $this->actingAs($admin)->getJson('/api/v1/admin/system/health/detail');
    $detailResponse->assertOk();
    $detailResponse->assertJsonPath('success', true);
    $detailResponse->assertJsonStructure([
        'data' => [
            'status',
            'checks' => [
                'database',
                'cache',
                'storage',
                'environment',
            ],
        ],
    ]);
});

it('allows admin to run live interactive self diagnostic test', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/system/health/test', [
        'type' => 'all',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.all_passed', true);
    expect($response->json('data.results.database.passed'))->toBeTrue();
    expect($response->json('data.results.cache.passed'))->toBeTrue();
    expect($response->json('data.results.storage.passed'))->toBeTrue();
});

it('allows admin to create, list, and delete backups', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Create a backup
    $customName = 'test_backup_suite_'.uniqid().'.sqlite';
    $createResponse = $this->actingAs($admin)->postJson('/api/v1/admin/system/backups', [
        'filename' => $customName,
    ]);

    $createResponse->assertOk();
    $createResponse->assertJsonPath('success', true);
    $createdFile = $createResponse->json('data.filename');
    expect($createdFile)->not->toBeNull();

    // 2. List backups
    $listResponse = $this->actingAs($admin)->getJson('/api/v1/admin/system/backups');
    $listResponse->assertOk();
    $filenames = collect($listResponse->json('data.backups'))->pluck('filename')->all();
    expect($filenames)->toContain($createdFile);

    // 3. Download backup
    $downloadResponse = $this->actingAs($admin)->get("/api/v1/admin/system/backups/{$createdFile}/download");
    $downloadResponse->assertOk();

    // 4. Delete backup
    $deleteResponse = $this->actingAs($admin)->deleteJson("/api/v1/admin/system/backups/{$createdFile}");
    $deleteResponse->assertOk();
    $deleteResponse->assertJsonPath('success', true);

    $verifyList = $this->actingAs($admin)->getJson('/api/v1/admin/system/backups');
    $remaining = collect($verifyList->json('data.backups'))->pluck('filename')->all();
    expect($remaining)->not->toContain($createdFile);
});

it('denies customers from accessing system health or backups', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->getJson('/api/v1/admin/system/overview')
        ->assertStatus(403);

    $this->actingAs($customer)
        ->getJson('/api/v1/admin/system/backups')
        ->assertStatus(403);
});
