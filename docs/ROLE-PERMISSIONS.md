# CandyCutz — Role & Permission Matrix

**Authoritative Architecture:** `ARCHITECTURE.md` §3  
**Framework:** Laravel 12 Policies & Middleware Guards

---

## 1. Role Definitions

| Role Identifier | Value in DB | Session Guard | Description |
| :--- | :--- | :--- | :--- |
| `super_admin` | `'super_admin'` | `check.role:super_admin` | Global platform administrator. Has unrestricted access across all shops, users, logs, and system operations. |
| `admin` | `'admin'` | `check.role:admin,super_admin` | Shop manager. Manages appointments, barbers, customers, payments, content, and local operational schedules. |
| `barber` | `'barber'` | `check.role:barber` | Staff barber. Manages their own chair status, appointment completions, venue payment receipts, and lookbook gallery. |
| `customer` | `'customer'` | `check.role:customer` | Registered grooming client. Books appointments, verifies attendance with verification codes, uploads payment receipts, and writes reviews. |

---

## 2. Granular Permission Capabilities

| Capability | Super Admin | Admin | Barber | Customer | Public / Guest |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Browse Services & Barbers** | Yes | Yes | Yes | Yes | Yes |
| **Book Appointment** | Yes | Yes | Yes (Walk-In) | Yes | Guest Flow |
| **Cancel Own Booking** | Yes | Yes | No | Yes (Allowed Window) | No |
| **Approve / Force-Approve Booking** | Yes | Yes | No | No | No |
| **Mark Booking Completed / No-Show** | Yes | Yes | Yes (Assigned) | No | No |
| **Verify Bank Transfer Payment** | Yes | Yes | Yes (Assigned) | No | No |
| **Reassign Barber to Booking** | Yes | Yes | No | No | No |
| **Manage Customers (CRUD, Status, Reset)**| Yes | Yes | No | No | No |
| **Manage Barbers (CRUD, Hours)** | Yes | Yes | Own Hours Only | No | No |
| **Manage CMS Website Content** | Yes | Yes | No | No | No |
| **Manage Blog Posts** | Yes | Yes | Own Posts Only | No | No |
| **Manage System Settings** | Yes | Yes | No | No | No |
| **Manage Users & Role Provisioning** | Yes | No | No | No | No |
| **View System Audit Logs** | Yes | No | No | No | No |
| **Execute System Backups & Telemetry** | Yes | Yes | No | No | No |
| **Change System Maintenance Mode** | Yes | No | No | No | No |

---

## 3. Authorization Guards & Middleware

1. **`auth:sanctum`**: Guarantees valid session authentication via encrypted cookies for SPA or Bearer token for mobile.
2. **`check.role:role1,role2`**: Enforces strict role membership. Unauthorized access returns `403 Forbidden` (`{"success":false,"message":"Forbidden: insufficient role permissions"}`).
3. **`manage-customers` Gate (`AppServiceProvider.php`)**: Protects administrative customer modifications (`admin` and `super_admin` only).
4. **`EnsureUserActive` Middleware**: Prevents suspended or deactivated users from accessing protected endpoints.

