# CandyCutz — Notification API Reference

All endpoints mount under `/api/v1/*` in adherence to `ARCHITECTURE.md`.

---

## 1. General & Customer Notification Endpoints

### `GET /api/v1/notifications`
Retrieve all notifications scoped to the authenticated user (including role-appropriate broadcast notices).
- **Query Parameters:**
  - `type`: optional filter (`all`, `booking`, `payment`, `review`, `announcement`).
- **Response:**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 142,
        "type": "booking_approved",
        "category": "booking",
        "priority": "normal",
        "title": "Booking Approved ✅",
        "message": "Your appointment for Signature Beard Trim on 2026-11-22 at 14:00 has been approved.",
        "action_url": "/customer/bookings/42",
        "data": { "booking_reference": "BK-1001" },
        "is_read": false,
        "created_at": "2026-11-21T14:00:00.000Z",
        "time_ago": "2 minutes ago"
      }
    ]
  }
  ```

### `GET /api/v1/notifications/unread-count`
Get count of unread notifications, evaluating direct unread items plus broadcast notices not present in `notification_reads` for the user.

### `PATCH /api/v1/notifications/{id}/read`
Mark a single notification as read. Direct notifications update `is_read = true`; broadcasts insert a record into `notification_reads`.

### `PATCH /api/v1/notifications/read-all`
Mark all direct and broadcast notifications as read for the user in a single atomic transaction.

### `DELETE /api/v1/notifications/{id}`
Dismiss or remove a notification.

### `GET /api/v1/notifications/stream`
Server-Sent Events real-time event stream.
- **Headers:**
  - `Accept: text/event-stream`
  - `Last-Event-ID`: optional integer ID for cursor recovery
- **Stream Output:**
  ```
  id: 142
  event: notification
  data: [{"id": 142, ...}]

  : ping
  ```

---

## 2. Notification Preferences Endpoints

### `GET /api/v1/notification-preferences`
Returns the user's category x channel preference matrix, mandatory locks, and quiet hours.
- **Response:**
  ```json
  {
    "success": true,
    "data": {
      "categories": [
        { "key": "security", "label": "Account & Security" },
        { "key": "booking", "label": "Appointments & Bookings" },
        { "key": "payment", "label": "Payments & Receipts" }
      ],
      "channels": [
        { "key": "in_app", "label": "In-App" },
        { "key": "push", "label": "Push" },
        { "key": "email", "label": "Email" }
      ],
      "preferences": {
        "security": { "in_app": true, "push": true, "email": true, "locked_channels": ["in_app", "email"] },
        "booking": { "in_app": true, "push": true, "email": true, "locked_channels": [] }
      },
      "quiet_hours": {
        "enabled": true,
        "start": "22:00",
        "end": "08:00"
      }
    }
  }
  ```

### `PUT /api/v1/notification-preferences`
Save updated category x channel preferences and quiet hours.
- **Payload:**
  ```json
  {
    "preferences": {
      "booking": { "in_app": true, "push": true, "email": false },
      "promotion": { "in_app": true, "push": false, "email": false }
    },
    "quiet_hours": {
      "enabled": true,
      "start": "23:00",
      "end": "07:00"
    }
  }
  ```

### `POST /api/v1/notifications/device-token`
Register device push token.
- **Payload:**
  ```json
  {
    "token": "ExponentPushToken[AbCdEf123456]",
    "platform": "ios",
    "app_version": "2.1.0"
  }
  ```

---

## 3. Super Admin CMS Endpoints

### `GET /api/v1/admin/notifications/deliveries`
Paginated audit logs of notification delivery across channels.
- **Query Parameters:** `channel` (`in_app`, `push`, `email`), `status` (`delivered`, `failed`, `suppressed`, `pending`), `search`, `page`, `per_page`.

### `GET /api/v1/admin/notifications/broadcasts`
List past broadcast campaigns and audience summaries.

### `POST /api/v1/admin/notifications/broadcasts`
Dispatch a targeted multi-channel broadcast campaign.
- **Payload:**
  ```json
  {
    "title": "Holiday Grooming Packages 🎄",
    "message": "Book your festive season styling appointments early.",
    "audience": "customers",
    "channels": ["in_app", "push", "email"],
    "action_url": "/booking"
  }
  ```

### `GET /api/v1/admin/notifications/analytics`
Telemetry metrics on 30-day volumes, delivery success rates by channel, platform device breakdown, and active user reach.
