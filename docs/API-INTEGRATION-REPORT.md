# CandyCutz — API & Integration Audit Report

**Authoritative Architecture:** `ARCHITECTURE.md` §4 & §6  
**Status:** Ground Truth Validated

---

## 1. External Integrations Assessment

### 1.1 Paystack Payment Gateway
* **Handler:** `app/Domain/Payment/Gateways/PaystackGateway.php`
* **Configuration:** `config/payments.php` via `PAYSTACK_SECRET_KEY`, `PAYSTACK_PUBLIC_KEY`
* **Currency Standard:** Nigerian Naira (NGN), stored and transferred as integer kobo (`1 NGN = 100 kobo`).
* **Webhook Signature:** Verified via `hash_hmac('sha512', $payload, $secret)` in `PaymentWebhookApiController.php`. Idempotency key tracking prevents duplicate transaction credit.
* **Health:** Operational.

### 1.2 Stripe Payment Gateway
* **Handler:** `app/Domain/Payment/Gateways/StripeGateway.php`
* **Configuration:** `config/services.php` via `STRIPE_SECRET`, `STRIPE_KEY`
* **Currency Standard:** USD / EUR / GBP / NGN international checkout.
* **Webhook Signature:** Verified via Stripe SDK Webhook Event construct.
* **Health:** Operational.

### 1.3 Manual Bank Transfer System
* **Handler:** `app/Domain/Payment/Gateways/ManualTransferGateway.php`
* **Workflow:**
  1. Customer selects manual bank transfer at checkout (`/api/v1/payments/confirm-transfer`).
  2. System displays verified shop or barber account details (`MANUAL_TRANSFER_BANK`, `MANUAL_TRANSFER_ACCOUNT_NAME`, `MANUAL_TRANSFER_ACCOUNT_NUMBER`).
  3. Customer uploads transfer receipt image (`/api/v1/payments/appointments/{id}/receipt`).
  4. Status transitions to `pending_verification`.
  5. Admin or assigned Barber reviews receipt in `ReceiptViewer.vue` and approves (`/api/v1/admin/payments/{id}/verify`).
  6. Status transitions to `completed` and appointment confirms.
* **Health:** Fully functional with robust file type validation.

### 1.4 Brevo Transactional Email Service
* **Handler:** `app/Domain/Notification/Services/BrevoApiClient.php`
* **Configuration:** `services.brevo.api_key` via environment variables.
* **Functionality:** Dispatches booking confirmations, OTP codes, verification badges, and password reset instructions.
* **Health:** Fallbacks to Laravel Log driver in local development when API key is unconfigured.

### 1.5 Google Social Authentication
* **Handler:** `AuthApiController@socialLogin`
* **Configuration:** `GOOGLE_CLIENT_IDS` (comma-separated support for web client and Android/iOS client IDs).
* **Token Verification:** Validates Google ID token against official Google API tokeninfo endpoint.
* **Health:** Operational.

---

## 2. API Design & Security Standards
1. **Single Mount Point:** 100% of API endpoints are mounted on `/api/v1/*`. Legacy un-versioned routes are deprecated.
2. **Response Envelope:** Every API endpoint returns standard JSON envelope:
   ```json
   {
     "success": true,
     "message": "Operation completed successfully",
     "data": { ... }
   }
   ```
3. **No Raw Secrets in Frontend:** API secret keys, database passwords, and webhook secrets reside strictly in `.env`.
4. **Idempotent Actions:** Payment webhooks and financial state changes use `idempotency_keys` table to guarantee exactly-once processing.

