# Candycutz — Architecture Essentials & Cheatsheet

## 1. Quick Reference Architecture

```
   Existing Vue 3 Web          Customer Mobile App          Barber Mobile App
            │                           │                           │
            └───────────────────────────┼───────────────────────────┘
                                        ▼
                            Laravel 11 API Gateway
                         (/api/v1/ via Sanctum Auth)
                                        │
           ┌────────────────────────────┼────────────────────────────┐
           ▼                            ▼                            ▼
   Booking Engine               Stripe Payments              Brevo Notifications
  (Row-Level Locks)          (Idempotent Webhooks)          (Queued Domain Events)
           │                            │                            │
           └────────────────────────────┼────────────────────────────┘
                                        ▼
                            MySQL 8.0 & Redis Engine
```

---

## 2. The 10 Inviolable Architectural Rules
1. **Never Trust Client Calculations**: Duration, pricing, travel fees, and availability are 100% computed server-side.
2. **Never Trust Client Payment Claims**: Payment is only confirmed when the authoritative Stripe webhook (`payment_intent.succeeded`) is verified and processed by the server.
3. **Pessimistic Row Locking for Bookings**: Use `SELECT ... FOR UPDATE` inside `DB::transaction` to eliminate double-booking race conditions.
4. **Soft Deactivation for Accounts**: Customers are never permanently deleted through normal application flows; set `status = 'deactivated'` and `deactivated_at = NOW()` to preserve audit and financial histories.
5. **Username-First Public Identity**: All public interactions display `@username`. Real names are stored privately.
6. **Immutable Audit Logs**: Every privileged Super Admin action must record actor, action, target, diff, IP, user-agent, and timestamp.
7. **Client Idempotency**: All mutating operations accept `X-Idempotency-Key` to safely handle network retries without duplicates.
8. **No Secrets on Mobile**: Secret keys (Stripe secret, Brevo API key, JWT secrets) live exclusively in backend `.env`.
9. **Unified API Contract**: All responses conform to `{ success: bool, message: string, data: any, meta?: any }`.
10. **Preserve Existing Web**: Never break or destroy the existing Vue 3 client; enhance and connect it to the converged API.

---

## 3. Directory Quick Reference

| Path | Purpose |
|---|---|
| `barbing-saloon-api/app/Services/` | Domain logic (Booking, Payment, Brevo, CMS) |
| `barbing-saloon-api/app/Http/Controllers/Api/V1/` | Versioned API v1 controllers |
| `barbing-saloon-api/database/migrations/` | Normalized database schema migrations |
| `barbing-saloon-web/` | Preserved Vue 3 Web Application |
| `candycutz-customer-app/` | Expo React Native Customer Mobile App |
| `candycutz-barber-app/` | Expo React Native Barber Mobile App |
| `docs/` | Authoritative platform specifications |
