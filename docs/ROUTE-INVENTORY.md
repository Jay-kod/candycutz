# CandyCutz — Comprehensive Route Inventory

**Mount Base:** `/api/v1/*`  
**Total Routes:** 318 Registered Routes  
**Authority:** `ARCHITECTURE.md` §2

---

## 1. Authentication Routes (`routes/api/v1/auth.php`)

| Method | Endpoint | Handler | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | `AuthApiController@login` | `throttle:auth-sensitive` | Credential login returning Sanctum token or SPA cookie |
| `POST` | `/api/v1/auth/register` | `AuthApiController@register` | `throttle:auth-sensitive` | Customer self-registration with OTP dispatch |
| `POST` | `/api/v1/auth/forgot-password` | `AuthApiController@forgotPassword` | `throttle:auth-sensitive` | Password reset link request |
| `POST` | `/api/v1/auth/reset-password` | `AuthApiController@resetPassword` | `throttle:auth-sensitive` | Tokenized password reset |
| `POST` | `/api/v1/auth/social-login` | `AuthApiController@socialLogin` | None | Google OAuth social login |
| `GET` | `/api/v1/auth/email/verify/{id}/{hash}` | `AuthApiController@verifyEmail` | `signed`, `throttle:6,1` | Email address cryptographic verification |
| `POST` | `/api/v1/auth/logout` | `AuthApiController@logout` | `auth:sanctum` | Revoke current user Sanctum access token |
| `POST` | `/api/v1/auth/logout-all` | `AuthApiController@logoutAll` | `auth:sanctum` | Revoke all active user tokens |
| `POST` | `/api/v1/auth/change-password` | `AuthApiController@changePassword` | `auth:sanctum` | Change password for authenticated session |
| `GET` | `/api/v1/auth/me` | `AuthApiController@me` | `auth:sanctum` | Return current authenticated user profile & role |

---

## 2. Public & Catalogue Routes (`routes/api/v1/catalogue.php`)

| Method | Endpoint | Handler | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/services` | `ServiceApiController@index` | List available grooming services |
| `GET` | `/api/v1/service-categories` | `ServiceApiController@categories` | List active service categories |
| `GET` | `/api/v1/services/{idOrSlug}` | `ServiceApiController@show` | Retrieve service details |
| `GET` | `/api/v1/barbers` | `BarberApiController@index` | List active barbers with ratings |
| `GET` | `/api/v1/barbers/{id}` | `BarberApiController@show` | Barber profile with portfolio lookbook |
| `GET` | `/api/v1/gallery` | `GalleryApiController@index` | Public gallery lookbook images |
| `GET` | `/api/v1/testimonials` | `TestimonialApiController@index` | Approved customer testimonials |
| `GET` | `/api/v1/blog` | `BlogApiController@index` | Published blog articles |
| `GET` | `/api/v1/blog/{slug}` | `BlogApiController@show` | Read full blog article |
| `GET` | `/api/v1/settings` | `PublicApiController@settings` | Public website brand & hero settings |
| `GET` | `/api/v1/working-hours` | `PublicApiController@workingHours` | Shop operating hours |
| `GET` | `/api/v1/available-slots` | `PublicApiController@availableSlots` | Booking slot engine |

---

## 3. Customer Portal Routes (`routes/api/v1/account.php`)

| Method | Endpoint | Handler | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/customer/dashboard` | `AccountApiController@dashboard` | Customer dashboard metrics, upcoming appointments |
| `GET` | `/api/v1/customer/profile` | `AccountApiController@profile` | Customer personal profile |
| `POST` | `/api/v1/customer/profile` | `AccountApiController@updateProfile` | Update personal profile |
| `GET` | `/api/v1/customer/my-codes` | `AccountApiController@myCodes` | Active appointment verification codes |
| `GET` | `/api/v1/customer/bookings` | `AppointmentApiController@index` | Customer appointment history |
| `POST` | `/api/v1/customer/bookings` | `AppointmentApiController@store` | Create new appointment booking |
| `PATCH` | `/api/v1/customer/bookings/{id}/cancel`| `AppointmentApiController@cancel` | Cancel customer appointment |
| `GET` | `/api/v1/customer/wishlist` | `WishlistApiController@index` | Customer saved styles |
| `POST` | `/api/v1/customer/wishlist` | `WishlistApiController@store` | Add service/style to wishlist |
| `GET` | `/api/v1/customer/notifications`| `NotificationController@index` | In-app customer notifications |

---

## 4. Barber Portal Routes (`routes/api/v1/account.php`)

| Method | Endpoint | Handler | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/barber/dashboard` | `BarberApiController@dashboard` | Barber chair metrics, today's queue |
| `GET` | `/api/v1/barber/appointments`| `AppointmentApiController@index` | Barber appointment schedule |
| `PATCH`| `/api/v1/barber/my-status` | `BarberApiController@updateMyStatus`| Toggle chair status (available/busy) |
| `PATCH`| `/api/v1/barber/appointments/{id}/complete` | `AppointmentApiController@complete` | Mark appointment completed |
| `PATCH`| `/api/v1/barber/appointments/{id}/no-show` | `AppointmentApiController@noShow` | Mark client as no-show |
| `POST` | `/api/v1/barber/appointments/walk-in` | `AppointmentApiController@storeWalkIn`| Add walk-in client to chair |
| `GET` | `/api/v1/barber/schedule` | `BarberApiController@schedule` | Weekly working shift hours |
| `PUT` | `/api/v1/barber/schedule` | `BarberApiController@updateSchedule`| Save working hours |

---

## 5. Admin Portal Routes (`routes/api/v1/admin.php`)

| Method | Endpoint | Handler | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/admin/dashboard` | `AdminApiController@dashboard` | Shop KPI stats, revenue, booking trends |
| `GET` | `/api/v1/admin/appointments` | `AppointmentApiController@index` | Master appointment registry |
| `POST` | `/api/v1/admin/appointments` | `AppointmentApiController@store` | Admin manual appointment dispatch |
| `POST` | `/api/v1/admin/appointments/walk-in` | `AppointmentApiController@storeWalkIn`| Create walk-in appointment |
| `PATCH`| `/api/v1/admin/appointments/{id}/approve` | `AppointmentApiController@approve` | Approve pending appointment |
| `GET` | `/api/v1/admin/customers` | `AdminApiController@customers` | Customer CRM list with active/status |
| `POST` | `/api/v1/admin/customers` | `AdminApiController@storeCustomer` | Admin create customer |
| `PUT` | `/api/v1/admin/customers/{id}` | `AdminApiController@updateCustomer`| Update customer details |
| `PATCH`| `/api/v1/admin/customers/{id}/status` | `AdminApiController@updateCustomerStatus` | Suspend or activate customer |
| `POST` | `/api/v1/admin/customers/{id}/reset-password` | `AdminApiController@resetCustomerPassword` | Reset customer password |
| `DELETE`| `/api/v1/admin/customers/{id}`| `AdminApiController@deleteCustomer`| Soft-delete customer |
| `GET` | `/api/v1/admin/barbers` | `BarberApiController@index` | Admin barber roster |
| `POST` | `/api/v1/admin/barbers` | `BarberApiController@store` | Create new barber |
| `GET` | `/api/v1/admin/payments` | `AdminPaymentApiController@index` | Payment verification queue |
| `POST` | `/api/v1/admin/payments/{id}/verify` | `AdminPaymentApiController@verifyPayment` | Verify bank transfer receipt |
| `POST` | `/api/v1/admin/payments/{id}/reassign-barber`| `AdminPaymentApiController@reassignBarber` | Reassign appointment barber |
| `GET` | `/api/v1/admin/reports` | `AdminApiController@reports` | Generate financial report |
| `GET` | `/api/v1/admin/system/health/detail` | `SystemHealthApiController@healthDetail` | Check DB, Redis, Cache status |

---

## 6. Super Admin Portal Routes (`routes/api/v1/admin.php`)

| Method | Endpoint | Handler | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/superadmin/dashboard`| `SuperAdminApiController@dashboard`| High-level platform governance |
| `GET` | `/api/v1/superadmin/users` | `SuperAdminApiController@users` | Global user directory (all roles) |
| `POST` | `/api/v1/superadmin/users` | `SuperAdminApiController@storeUser`| Provision admin or staff user |
| `PATCH`| `/api/v1/superadmin/users/{user}/activate` | `SuperAdminApiController@activateUser` | Activate user account |
| `PATCH`| `/api/v1/superadmin/users/{user}/deactivate` | `SuperAdminApiController@deactivateUser` | Deactivate user account |
| `GET` | `/api/v1/superadmin/audit-logs`| `SuperAdminApiController@auditLogs`| System audit trail |
| `GET` | `/api/v1/superadmin/settings` | `SuperAdminApiController@settings` | Platform global configuration |

