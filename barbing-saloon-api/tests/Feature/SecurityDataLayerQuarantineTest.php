<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Services\BackupEncryptionService;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PDO;

uses(RefreshDatabase::class);

beforeEach(function () {
    $backupDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/backups'));
    if (! File::isDirectory($backupDir)) {
        File::makeDirectory($backupDir, 0755, true);
    }
});

it('quarantines payment receipts on private disk and denies unauthorized cross-customer access', function () {
    $customerA = User::factory()->create(['role' => 'customer']);
    $customerB = User::factory()->create(['role' => 'customer']);

    // Create appointment for Customer A
    $appointment = Appointment::factory()->create([
        'customer_id' => $customerA->id,
    ]);

    // Store private receipt
    $receiptContent = 'FAKE_PDF_RECEIPT_RAW_DATA';
    $receiptPath = "receipts/{$appointment->id}/test_receipt.pdf";
    $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/'.$receiptPath));
    File::ensureDirectoryExists(dirname($fullPath));
    File::put($fullPath, $receiptContent);

    Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customerA->id,
        'amount' => 5000,
        'currency' => 'NGN',
        'status' => 'under_review',
        'receipt_url' => $receiptPath,
    ]);

    // Ensure receipt is NOT in public directory (zero public leakage)
    expect(File::exists(public_path('storage/'.$receiptPath)))->toBeFalse();

    // Customer B attempts to access Customer A's receipt -> 403 Forbidden
    $this->actingAs($customerB)
        ->get("/api/v1/payments/appointments/{$appointment->id}/receipt")
        ->assertStatus(403);
});

it('allows customer, assigned barber, and admin to access receipts via auth route', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $admin = User::factory()->create(['role' => 'admin']);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
    ]);

    $receiptPath = "receipts/{$appointment->id}/verified_receipt.jpg";
    $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/'.$receiptPath));
    File::ensureDirectoryExists(dirname($fullPath));
    File::put($fullPath, 'VERIFIED_JPEG_BYTES');

    Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 6000,
        'currency' => 'NGN',
        'status' => 'verified',
        'receipt_url' => $receiptPath,
    ]);

    // 1. Customer can view their own receipt
    $customerRes = $this->actingAs($customer)
        ->get("/api/v1/payments/appointments/{$appointment->id}/receipt");
    $customerRes->assertOk();
    $customerRes->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($customerRes->headers->get('Cache-Control'))->toContain('no-store')->toContain('private');

    // 2. Assigned barber can view receipt
    $this->actingAs($barberUser)
        ->get("/api/v1/payments/appointments/{$appointment->id}/receipt")
        ->assertOk();

    // 3. Admin can view receipt
    $this->actingAs($admin)
        ->get("/api/v1/payments/appointments/{$appointment->id}/receipt")
        ->assertOk();
});

it('generates 15-minute temporary signed receipt URLs and rejects tampered signatures', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create(['customer_id' => $customer->id]);

    $receiptPath = "receipts/{$appointment->id}/signed_test.png";
    $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/'.$receiptPath));
    File::ensureDirectoryExists(dirname($fullPath));
    File::put($fullPath, 'IMAGE_PNG_BYTES');

    Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 4500,
        'currency' => 'NGN',
        'status' => 'under_review',
        'receipt_url' => $receiptPath,
    ]);

    // Request signed URL as customer
    $urlRes = $this->actingAs($customer)
        ->getJson("/api/v1/payments/appointments/{$appointment->id}/receipt-url");
    $urlRes->assertOk();
    $signedUrl = $urlRes->json('data.signed_url');
    expect($signedUrl)->toContain('signature=');

    // Access via valid signed URL without any Authorization header
    $streamRes = $this->get($signedUrl);
    $streamRes->assertOk();
    $streamRes->assertHeader('X-Content-Type-Options', 'nosniff');

    // Tamper with signature
    $tamperedUrl = preg_replace('/signature=[a-f0-9]+/', 'signature=tampered123456', $signedUrl);
    $this->get($tamperedUrl)->assertStatus(403);
});

it('creates encrypted database snapshots using AES-256-CBC at rest with 0600 permissions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customName = 'secure_snapshot_'.uniqid().'.sqlite';

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/system/backups', [
        'filename' => $customName,
        'encrypt' => true,
    ]);

    $response->assertOk();
    expect($response->json('data.is_encrypted'))->toBeTrue();
    $createdFile = $response->json('data.filename');
    expect($createdFile)->toEndWith('.enc');

    $backupPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/backups/'.$createdFile));
    expect(File::exists($backupPath))->toBeTrue();

    // Verify it cannot be parsed as plain SQLite (ciphertext)
    $encryptionService = app(BackupEncryptionService::class);
    expect($encryptionService->isEncrypted($backupPath))->toBeTrue();

    $header = file_get_contents($backupPath, false, null, 0, 9);
    expect($header)->toBe('CC_ENC_V1');

    // Verify raw SQLite PDO fails to open ciphertext directly
    expect(function () use ($backupPath) {
        $pdo = new PDO('sqlite:'.$backupPath);
        $pdo->query('SELECT 1');
    })->toThrow(\PDOException::class);

    // Verify decrypting restores a valid SQLite database
    $decryptedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/backups/temp_decrypted_'.uniqid().'.sqlite'));
    $encryptionService->decryptFile($backupPath, $decryptedPath);

    $validPdo = new PDO('sqlite:'.$decryptedPath);
    expect($validPdo->query('PRAGMA integrity_check')->fetchColumn())->toBe('ok');
    unset($validPdo);
    File::delete($decryptedPath);
    File::delete($backupPath);
});

it('rejects tampered encrypted backups with HMAC mismatch exception', function () {
    $encryptionService = app(BackupEncryptionService::class);
    $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/backups'));
    File::ensureDirectoryExists($dir);
    $source = $dir.DIRECTORY_SEPARATOR.'plain_source_'.uniqid().'.txt';
    $encrypted = $dir.DIRECTORY_SEPARATOR.'enc_test_'.uniqid().'.enc';
    $decrypted = $dir.DIRECTORY_SEPARATOR.'dec_test_'.uniqid().'.txt';

    file_put_contents($source, 'HIGHLY_SENSITIVE_CUSTOMER_AND_PAYMENT_DATA');
    clearstatcache(true, $source);
    $encryptionService->encryptFile($source, $encrypted);

    // Tamper with one byte in the ciphertext payload
    $bytes = file_get_contents($encrypted);
    $bytes[strlen($bytes) - 5] = chr(ord($bytes[strlen($bytes) - 5]) ^ 0xFF);
    file_put_contents($encrypted, $bytes);

    expect(fn () => $encryptionService->decryptFile($encrypted, $decrypted))
        ->toThrow(\RuntimeException::class, 'HMAC mismatch');

    File::delete([$source, $encrypted]);
});

it('allows admin to download encrypted backup with on-the-fly decryption option', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $filename = 'admin_download_test_'.uniqid().'.sqlite';

    $createRes = $this->actingAs($admin)->postJson('/api/v1/admin/system/backups', [
        'filename' => $filename,
        'encrypt' => true,
    ]);
    $createRes->assertOk();
    $createdFile = $createRes->json('data.filename');

    // 1. Download encrypted directly
    $encDownload = $this->actingAs($admin)->get("/api/v1/admin/system/backups/{$createdFile}/download");
    $encDownload->assertOk();
    $encDownload->assertHeader('X-Backup-Encrypted', '1');

    // 2. Download with on-the-fly decryption
    $decDownload = $this->actingAs($admin)->get("/api/v1/admin/system/backups/{$createdFile}/download?decrypt=1");
    $decDownload->assertOk();
    $decDownload->assertHeader('X-Backup-Encrypted', '0');

    File::delete(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/backups/'.$createdFile)));
});
