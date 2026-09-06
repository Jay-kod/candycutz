# Candycutz — Testing Strategy & Quality Assurance

## 1. Testing Philosophy
A feature in Candycutz is never marked complete merely because its happy path executes. Quality assurance validates edge cases, network dropouts, concurrent race conditions, authorization leaks, and malicious inputs.

---

## 2. Backend Automated Test Suite (PHPUnit / Pest)

### 2.1 Concurrency & Race Condition Test (`tests/Feature/BookingConcurrencyTest.php`)
```php
public function test_two_users_cannot_book_the_same_slot_simultaneously(): void
{
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['duration_minutes' => 30]);
    $date = now()->addDays(2)->toDateString();
    $time = '14:00:00';

    $user1 = User::factory()->create(['role' => 'customer']);
    $user2 = User::factory()->create(['role' => 'customer']);

    // Attempt first booking
    $response1 = $this->actingAs($user1, 'sanctum')->postJson('/api/v1/customer/bookings', [
        'barber_id' => $barber->id,
        'services' => [['id' => $service->id, 'price' => 25.00, 'duration_minutes' => 30]],
        'date' => $date,
        'start_time' => $time,
        'end_time' => '14:30:00',
        'type' => 'in_shop',
        'total_duration' => 30,
        'grand_total' => 25.00,
    ]);

    $response1->assertStatus(201);

    // Attempt colliding booking for identical slot
    $response2 = $this->actingAs($user2, 'sanctum')->postJson('/api/v1/customer/bookings', [
        'barber_id' => $barber->id,
        'services' => [['id' => $service->id, 'price' => 25.00, 'duration_minutes' => 30]],
        'date' => $date,
        'start_time' => $time,
        'end_time' => '14:30:00',
        'type' => 'in_shop',
        'total_duration' => 30,
        'grand_total' => 25.00,
    ]);

    $response2->assertStatus(409); // Conflict
    $this->assertDatabaseCount('appointments', 1);
}
```

### 2.2 Identity & Username Cooldown Test (`tests/Feature/UsernameRulesTest.php`)
```php
public function test_username_cannot_be_changed_within_90_days(): void
{
    $user = User::factory()->create([
        'username' => 'originalhandle',
        'last_username_change_at' => now()->subDays(30), // Changed 30 days ago
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/auth/username', [
        'username' => 'newhandle',
    ]);

    $response->assertStatus(422)
             ->assertJsonFragment(['error_code' => 'USERNAME_COOLDOWN_ACTIVE']);
}
```

### 2.3 Stripe Webhook Idempotency Test (`tests/Feature/StripeWebhookTest.php`)
```php
public function test_duplicate_webhook_events_are_ignored(): void
{
    $appointment = Appointment::factory()->create(['status' => 'pending']);
    $payment = Payment::factory()->create(['appointment_id' => $appointment->id, 'stripe_payment_intent_id' => 'pi_test123']);

    $payload = [
        'id' => 'evt_test_unique_123',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test123',
                'amount' => 5000,
            ]
        ]
    ];

    // First dispatch
    $response1 = $this->postJson('/api/v1/payments/webhook/stripe', $payload);
    $response1->assertStatus(200);
    $this->assertEquals('confirmed', $appointment->fresh()->status);

    // Second duplicate dispatch
    $response2 = $this->postJson('/api/v1/payments/webhook/stripe', $payload);
    $response2->assertStatus(200);
    $this->assertDatabaseCount('payment_transactions', 1); // Not duplicated
}
```

---

## 3. Mobile Automated Verification

### 3.1 TypeScript Static Analysis
Executed on both mobile codebases:
```bash
cd candycutz-customer-app && npx tsc --noEmit
cd candycutz-barber-app && npx tsc --noEmit
```
Zero type errors permitted in committed code.

### 3.2 Form Validation Scenarios (Zod / React Hook Form)
- Invalid email formatting rejected before network call.
- Passwords < 8 characters blocked.
- Username with forbidden characters (`@`, spaces, emojis) flagged immediately in UI.
- Home service address without street landmark rejected.

---

## 4. Manual QA Test Matrix

| Test ID | Test Scenario | Expected Outcome |
|---|---|---|
| **QA-01** | Book In-Shop appointment with specific barber | Appointment created, status `confirmed`, Brevo email dispatched. |
| **QA-02** | Book Home Service appointment with address in Keffi | Zone travel fee added to grand total, location masked until T - 2h. |
| **QA-03** | Barber marks appointment as `completed` | Status transitions, customer prompted for star review, address re-masked. |
| **QA-04** | Customer deactivates account | App reports "Account Deleted", historical records preserved in database. |
| **QA-05** | Super Admin overrides schedule in God Mode | Schedule updated immediately, audit log entry created with reason. |
| **QA-06** | Super Admin modifies brand color in Theme Studio | Draft saved, previewed, published; web and mobile re-hydrate new token. |
