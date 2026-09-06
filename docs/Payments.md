# Candycutz — Payments & Stripe Integration Architecture

## 1. Overview & Security Mandates
Stripe is the authoritative payment infrastructure for Candycutz, processing card payments, Apple Pay, and Google Pay in supported currencies.

### 1.1 Inviolable Rules
1. **Never Trust Mobile Clients**: The client never determines whether a payment succeeded. Only a verified, cryptographically signed Stripe webhook event confirms payment.
2. **Zero Raw Card Data**: No credit card numbers, CVVs, or expiration dates ever touch Candycutz servers. All entry is handled via Stripe Elements or the native Stripe PaymentSheet.
3. **Strict Secrets Isolation**: Stripe secret keys (`sk_live_...`, `whsec_...`) reside solely in the backend `.env`.

---

## 2. Architecture & Service Abstraction

```mermaid
graph TD
    subgraph ClientLayer ["Client Apps"]
        CUST_APP["Customer App / Web"]
    end

    subgraph ServiceLayer ["Laravel Service Abstraction"]
        PAY_SRV["PaymentService"]
        STRIPE_PROV["StripePaymentProvider"]
        INTENT_SRV["PaymentIntentService"]
        WEBHOOK_HND["WebhookHandler"]
        REFUND_SRV["RefundService"]
    end

    subgraph StripeCloud ["Stripe Cloud Platform"]
        STRIPE_API["Stripe REST API"]
        STRIPE_HOOK["Stripe Webhooks"]
    end

    subgraph DataStore ["Database"]
        DB_PAY["payments table"]
        DB_TX["payment_transactions table"]
        DB_APP["appointments table"]
    end

    CUST_APP -->|1. Initialize Checkout| PAY_SRV
    PAY_SRV --> INTENT_SRV
    INTENT_SRV --> STRIPE_PROV
    STRIPE_PROV -->|2. Create PaymentIntent| STRIPE_API
    STRIPE_API -->> CUST_APP: 3. Return client_secret
    CUST_APP ->> STRIPE_API: 4. Authorize Payment (Native Sheet)
    
    STRIPE_HOOK -.->|5. payment_intent.succeeded| WEBHOOK_HND
    WEBHOOK_HND -->|6. Idempotent Verify & Commit| DB_PAY
    WEBHOOK_HND --> DB_TX
    WEBHOOK_HND -->|7. Confirm Booking| DB_APP
```

---

## 3. Idempotent Webhook Processing

To handle duplicate or retried webhooks safely:
```php
public function handleStripeWebhook(Request $request): JsonResponse
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $secret = config('services.stripe.webhook_secret');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
    } catch (\UnexpectedValueException | \Stripe\Exception\SignatureVerificationException $e) {
        return response()->json(['error' => 'Invalid webhook signature'], 400);
    }

    // Idempotency check: Have we already processed this exact event?
    $exists = PaymentTransaction::where('gateway_event_id', $event->id)->exists();
    if ($exists) {
        return response()->json(['status' => 'already_processed'], 200);
    }

    if ($event->type === 'payment_intent.succeeded') {
        $paymentIntent = $event->data->object;
        
        DB::transaction(function () use ($paymentIntent, $event) {
            $appointmentDraftId = $paymentIntent->metadata->appointment_draft_id ?? null;
            
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->firstOrFail();
            $payment->update([
                'status' => 'successful',
                'stripe_charge_id' => $paymentIntent->latest_charge ?? null,
            ]);

            PaymentTransaction::create([
                'payment_id' => $payment->id,
                'transaction_type' => 'capture',
                'gateway' => 'stripe',
                'gateway_event_id' => $event->id,
                'amount' => $paymentIntent->amount / 100,
                'raw_payload' => $event->jsonSerialize(),
                'status' => 'success',
            ]);

            // Transition appointment from pending_payment to confirmed
            $appointment = $payment->appointment;
            $appointment->update(['status' => 'confirmed']);

            // Dispatch domain event for notifications
            event(new BookingPaymentConfirmed($appointment, $payment));
        });
    }

    return response()->json(['status' => 'success'], 200);
}
```

---

## 4. Refund Lifecycle
- If an appointment is cancelled $\ge$ 4 hours prior, the system triggers `RefundService::issueRefund($appointment)`.
- Calls `\Stripe\Refund::create(['payment_intent' => $payment->stripe_payment_intent_id])`.
- Automatically logs a `refund` record in `payment_transactions` and dispatches a Brevo notification to the customer.
