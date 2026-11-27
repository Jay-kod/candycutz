<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Services\AuditLogger;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditTrailTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_user_activation_and_deactivation_emits_audit_logs(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $targetUser = User::factory()->create(['role' => 'barber', 'is_active' => true, 'status' => 'active']);

        // 1. Deactivate user
        $response = $this->actingAs($superAdmin)->patchJson("/api/v1/super-admin/users/{$targetUser->id}/deactivate");
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'user_deactivated',
            'module' => 'SuperAdmin',
            'target_type' => 'User',
            'target_id' => $targetUser->id,
        ]);

        // 2. Activate user
        $response2 = $this->actingAs($superAdmin)->patchJson("/api/v1/super-admin/users/{$targetUser->id}/activate");
        $response2->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'user_activated',
            'module' => 'SuperAdmin',
            'target_type' => 'User',
            'target_id' => $targetUser->id,
        ]);
    }

    public function test_superadmin_password_reset_records_security_audit_event(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $targetUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($superAdmin)->postJson("/api/v1/super-admin/users/{$targetUser->id}/reset-password", [
            'password' => 'NewHardenedPassword123!',
            'password_confirmation' => 'NewHardenedPassword123!',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'password_reset',
            'module' => 'SuperAdmin',
            'target_type' => 'User',
            'target_id' => $targetUser->id,
        ]);
    }

    public function test_admin_customer_status_change_and_password_reset_records_audit_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        // Suspend customer
        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/customers/{$customer->id}/status", [
            'status' => 'suspended',
            'is_active' => false,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'customer_status_updated',
            'module' => 'CustomerManagement',
            'target_type' => 'User',
            'target_id' => $customer->id,
        ]);

        // Reset password
        $response2 = $this->actingAs($admin)->postJson("/api/v1/admin/customers/{$customer->id}/reset-password", [
            'password' => 'NewCustomerPass456!',
        ]);
        $response2->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'customer_password_reset',
            'module' => 'CustomerManagement',
            'target_type' => 'User',
            'target_id' => $customer->id,
        ]);
    }

    public function test_system_backup_lifecycle_emits_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create backup
        $response = $this->actingAs($admin)->postJson('/api/v1/admin/system/backups', [
            'filename' => 'audit_test_snapshot',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'backup_created',
            'module' => 'SystemBackup',
        ]);

        $createdBackup = AuditLog::where('action', 'backup_created')->first();
        $this->assertNotNull($createdBackup);
        $filename = $createdBackup->new_value['filename'];

        // Download backup
        $downloadRes = $this->actingAs($admin)->get("/api/v1/admin/system/backups/{$filename}/download");
        $this->assertTrue(in_array($downloadRes->getStatusCode(), [200, 302], true));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'backup_downloaded',
            'module' => 'SystemBackup',
        ]);

        // Delete backup
        $delRes = $this->actingAs($admin)->deleteJson("/api/v1/admin/system/backups/{$filename}");
        $delRes->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'backup_deleted',
            'module' => 'SystemBackup',
        ]);
    }

    public function test_system_health_circuit_breaker_reset_records_audit_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/system/circuit-breakers/paystack/reset');
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'circuit_breaker_reset',
            'module' => 'SystemHealth',
        ]);
    }

    public function test_admin_and_superadmin_can_retrieve_audit_logs_with_rich_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Sarah Admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'name' => 'Root SuperAdmin']);

        // Log an audit event
        AuditLogger::log('setting.updated', 'Settings', null, ['maintenance' => false], ['maintenance' => true]);

        // 1. Admin logs endpoint
        $adminRes = $this->actingAs($admin)->getJson('/api/v1/admin/logs');
        $adminRes->assertStatus(200);
        $adminRes->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'action', 'entity_type', 'created_at'],
            ],
        ]);

        // 2. Superadmin audit-logs endpoint
        $superAdminRes = $this->actingAs($superAdmin)->getJson('/api/v1/super-admin/audit-logs');
        $superAdminRes->assertStatus(200);
        $superAdminRes->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'action', 'module'],
            ],
            'meta' => ['current_page', 'total'],
        ]);
    }

    public function test_customers_are_strictly_forbidden_from_audit_logs(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $res1 = $this->actingAs($customer)->getJson('/api/v1/admin/logs');
        $this->assertTrue(in_array($res1->getStatusCode(), [401, 403], true));

        $res2 = $this->actingAs($customer)->getJson('/api/v1/super-admin/audit-logs');
        $this->assertTrue(in_array($res2->getStatusCode(), [401, 403], true));
    }
}
