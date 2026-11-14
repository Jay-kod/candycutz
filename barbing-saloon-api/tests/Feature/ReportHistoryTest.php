<?php

use App\Models\AuditLog;
use App\Models\User;

it('allows admin to log and retrieve report export history', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Initial history retrieval (empty or base logs)
    $res = $this->actingAs($admin)->getJson('/api/v1/admin/reports/history');
    $res->assertStatus(200);
    $res->assertJsonPath('success', true);

    // 2. Log a PDF export event
    $logPayload = [
        'action' => 'report_pdf_download',
        'report_id' => 'REF-CCZ-20261031-TEST',
        'range' => '30d',
        'range_label' => '30 Days',
        'title' => 'Executive Operations Dossier',
        'metadata' => [
            'reconciled_revenue' => 1250000,
            'completed_appointments' => 140,
        ],
        'checksum' => 'SHA256:test12345678',
    ];

    $logRes = $this->actingAs($admin)->postJson('/api/v1/admin/reports/log', $logPayload);
    $logRes->assertStatus(201);
    $logRes->assertJsonPath('success', true);
    $logRes->assertJsonPath('data.report_id', 'REF-CCZ-20261031-TEST');
    $logRes->assertJsonPath('data.action', 'report_pdf_download');

    // 3. Verify in database
    expect(AuditLog::where('module', 'reports')->where('action', 'report_pdf_download')->exists())->toBeTrue();

    // 4. Retrieve history and ensure new entry appears
    $historyRes = $this->actingAs($admin)->getJson('/api/v1/admin/reports/history');
    $historyRes->assertStatus(200);
    $records = collect($historyRes->json('data'));
    $matched = $records->firstWhere('report_id', 'REF-CCZ-20261031-TEST');

    expect($matched)->not->toBeNull();
    expect($matched['action'])->toBe('report_pdf_download');
    expect($matched['action_label'])->toBe('PDF Document Download');
    expect($matched['verified'])->toBeTrue();
});

it('denies customer from logging or viewing report history', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $getRes = $this->actingAs($customer)->getJson('/api/v1/admin/reports/history');
    $getRes->assertStatus(403);

    $postRes = $this->actingAs($customer)->postJson('/api/v1/admin/reports/log', [
        'action' => 'report_pdf_download',
        'report_id' => 'REF-CCZ-HACK',
    ]);
    $postRes->assertStatus(403);
});

