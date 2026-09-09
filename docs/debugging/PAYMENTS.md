# Candycutz — Payments & Stripe Debugging Runbook

## 1. Problem: Fatal Error "Class 'Stripe\Stripe' not found"

### What it looks like:
When attempting an online payment, the API returns a 500 Internal Server Error:
> `Error: Class "Stripe\Stripe" not found in StripePaymentProvider.php`

### Root Cause:
`stripe/stripe-php` was not installed in `composer.json`.

### How to Fix:
Install the official Stripe PHP SDK inside the backend container:
```bash
docker compose exec app composer require stripe/stripe-php
```

---

## 2. Problem: Appointment Remains Stuck in "Pending Payment"

### What it looks like:
The customer completes credit card payment on Stripe Checkout or in-app payment sheet, but their appointment never updates to `confirmed`.

### Root Cause:
The Stripe webhook (`payment_intent.succeeded`) was not received or failed cryptographic signature verification.

### How to Diagnose:
1. Inspect the Stripe Dashboard Webhook Logs under **Developers -> Webhooks**.
2. Look for the webhook URL (`https://api.candycutz.com/api/v1/payments/webhook`).
3. Check the HTTP response status:
   - If `404 Not Found`: The route is missing or misconfigured.
   - If `400 Bad Request`: The `STRIPE_WEBHOOK_SECRET` in `.env` does not match the signing secret in the Stripe dashboard.

### How to Fix:
1. Copy the signing secret (`whsec_...`) from Stripe Dashboard.
2. Update `.env`:
   ```ini
   STRIPE_WEBHOOK_SECRET=whsec_your_actual_webhook_signing_secret
   ```
3. Clear Laravel config cache:
   ```bash
   docker compose exec app php artisan config:clear
   ```

---

## 3. Testing Webhooks Locally via Stripe CLI

To test webhook processing on your local development computer without deploying to the public internet:
```bash
# 1. Login to your Stripe test account
stripe login

# 2. Forward webhooks directly to your local Laravel server
stripe listen --forward-to localhost:8000/api/v1/payments/webhook

# 3. Copy the printed webhook signing secret into your local .env:
# > Ready! Your webhook signing secret is whsec_xxxxxxxxxx
```
In a second terminal, trigger a test event:
```bash
stripe trigger payment_intent.succeeded
```
The terminal will display `200 OK` from Laravel, and the corresponding appointment will atomically transition to `confirmed`.
