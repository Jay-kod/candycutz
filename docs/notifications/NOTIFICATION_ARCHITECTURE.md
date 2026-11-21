# CandyCutz — Unified Notification Engine Architecture

## 1. High-Level Architecture

The CandyCutz Unified Notification Engine is an authoritative, multi-channel notification infrastructure that handles notification generation, user preference evaluation, delivery tracking, and client sync across the Vue 3 PWA, Expo mobile app, and Super Admin CMS.

```
+-------------------------------------------------------------------------------+
|                             Business Domain Events                            |
| (Bookings, Payments, Reviews, Category Requests, Auth/Security, Announcements) |
+-------------------------------------------------------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                      NotificationDispatcher (Central Hub)                     |
|  - Idempotency Deduplication (SHA-256 event hash)                             |
|  - Recipient Resolution (Customer, Barber, Admin)                             |
|  - Category Classification & Prioritization                                   |
|  - Preference & Quiet Hours Evaluation                                        |
+-------------------------------------------------------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                          Canonical Persistence Layer                          |
|  - notifications table (Authoritative source of truth)                        |
|  - notification_reads (Per-user broadcast read receipts)                      |
|  - notification_deliveries (Per-channel delivery tracking)                     |
|  - notification_broadcasts (Campaign history & telemetry)                     |
|  - user_notification_preferences (Category x Channel matrix)                  |
+-------------------------------------------------------------------------------+
                                       |
              +------------------------+------------------------+
              |                        |                        |
              v                        v                        v
+--------------------------+ +--------------------+ +--------------------------+
|  In-App DB Channel       | |  Expo Push Channel | |  Brevo Email Channel     |
|  - Direct user receipts  | |  - Batched (100)   | |  - Queued SendBrevoEmail |
|  - Status: delivered     | |  - SendExpoPush    | |  - 3 retries, backoff    |
|  - SSE stream real-time  | |  - Quiet hours     | |  - Status: delivered/fail|
+--------------------------+ +--------------------+ +--------------------------+
              |                        |                        |
              +------------------------+------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                             Client Applications                               |
|  - Vue 3 PWA: NotificationBell, useNotifications, SSE with Last-Event-ID      |
|  - Admin CMS: NotificationsPage (Stream, Deliveries table, Broadcasts, Stats) |
|  - Customer: NotificationSettingsPage (Matrix toggles, Quiet hours)           |
|  - Expo Mobile: Push token receiver & deep link handlers                      |
+-------------------------------------------------------------------------------+
```

---

## 2. Channel Independence & Failure Domains

Each delivery channel operates in strict isolation:
- **In-App:** Written synchronously to the primary MySQL database. Immediate authoritative record.
- **Expo Push:** Handled via queued `SendExpoPush` job. Bounced tokens (`DeviceNotRegistered`) automatically revoke the device token without interrupting user notifications.
- **Brevo Email:** Queued via `SendBrevoEmail` job with exponential backoff (15s, 60s, 300s). External SMTP latency or rate limits never delay HTTP API responses or prevent in-app notification creation.
- **SSE Stream:** Authenticated real-time delivery with reconnect cursors (`Last-Event-ID`). When dropped by network, client falls back smoothly to reference-counted adaptive polling.

---

## 3. Idempotency & Deduplication Strategy

To protect against duplicate payment webhooks, double form submissions, and network retries:
1. Every domain event generates or receives an `idempotency_key` (SHA-256 hash of event type, target entity ID, and timestamp/status).
2. `NotificationDispatcher::dispatchNotification` checks `Notification::where('idempotency_key', $key)->first()` before persistence.
3. If an idempotency match is found, the existing record is returned immediately without firing duplicate push or email delivery jobs.

---

## 4. Compliance & Mandatory Message Protection

The preference evaluation engine enforces strict server-side rules:
- **Mandatory Alerts:** `security` and `payment` alerts in the In-App and Email channels can never be disabled by user preferences.
- **Quiet Hours:** During quiet hours (e.g. 22:00 to 08:00), non-urgent push notifications are marked as `'suppressed'` with reason `'Suppressed by user quiet hours'`. Critical security notices and payment failures bypass quiet hours and deliver immediately.
