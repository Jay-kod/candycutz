# Candycutz — Routing Specification & Navigation Contracts

## 1. Routing Principles & Client Separation

Candycutz strictly separates routing responsibilities by client tier:
- **API Layer**: Exposes canonical, versioned `/api/v1/*` endpoints organized by business domain.
- **Web Client**: Uses Vue Router HTML5 history mode with module route guards.
- **Mobile Client**: Uses Expo Router with server-verified role-based root layout switching.
- **CMS Admin**: Accessible via `/admin/*` on the web client with Super Admin authorization.

---

## 2. Canonical API v1 Routing Matrix (`/api/v1/*`)

All mobile clients, external consumers, and modern frontend features communicate exclusively through the `/api/v1/` prefix.

| Method | Endpoint | Access / Role | Domain Controller | Description |
|---|---|---|---|---|
| **POST** | `/api/v1/auth/login` | Public | `AuthController@login` | Authenticate via email, username, or phone |
| **POST** | `/api/v1/auth/register` | Public | `AuthController@register` | Customer registration with unique `@username` |
| **POST** | `/api/v1/auth/social-login` | Public | `AuthController@socialLogin` | Google / Apple ID token sign-in |
| **POST** | `/api/v1/auth/forgot-password`| Public | `AuthController@forgotPassword`| Request password reset link |
| **POST** | `/api/v1/auth/reset-password` | Public | `AuthController@resetPassword` | Complete password reset |
| **GET** | `/api/v1/auth/me` | Authenticated | `AuthController@me` | Get active user profile, role, and permissions |
| **POST** | `/api/v1/auth/logout` | Authenticated | `AuthController@logout` | Revoke current device access token |
| **GET** | `/api/v1/services` | Public | `ServicesController@index` | List active services, prices, and durations |
| **GET** | `/api/v1/services/{slug}` | Public | `ServicesController@show` | Single service details |
| **GET** | `/api/v1/service-categories` | Public | `ServicesController@categories`| List categories (Haircuts, Beard, VIP) |
| **GET** | `/api/v1/barbers` | Public | `BarbersController@index` | List active master barbers & chair status |
| **GET** | `/api/v1/barbers/{id}` | Public | `BarbersController@show` | Single barber bio, specialties, portfolio |
| **GET** | `/api/v1/availability` | Public | `AvailabilityController@index` | Available time slots for date & duration |
| **GET** | `/api/v1/service-zones` | Public | `HomeServicesController@zones` | Keffi home service delivery zones & fees |
| **POST** | `/api/v1/appointments` | Customer | `AppointmentsController@store` | Atomically book slot with row-level lock |
| **GET** | `/api/v1/appointments` | Authenticated | `AppointmentsController@index` | Role-filtered appointments (Customer/Barber)|
| **GET** | `/api/v1/appointments/{id}`| Authenticated | `AppointmentsController@show` | Single appointment details & receipt |
| **PATCH**| `/api/v1/appointments/{id}/cancel`| Authenticated|`AppointmentsController@cancel`| Cancel appointment with reason |
| **PATCH**| `/api/v1/appointments/{id}/status`| Barber/Admin |`AppointmentsController@updateStatus`| Check-In, In-Chair, Complete, No-Show |
| **POST** | `/api/v1/appointments/walk-in`| Barber/Admin| `AppointmentsController@storeWalkIn`| 30-second quick walk-in appointment |
| **GET** | `/api/v1/barber/schedule` | Barber | `BarberScheduleController@index`| Barber's 7-day working hours & blocks |
| **PUT** | `/api/v1/barber/schedule` | Barber | `BarberScheduleController@update`| Update 7-day working hours |
| **POST** | `/api/v1/barber/block-time`| Barber | `BarberScheduleController@blockTime`| Block personal time / holiday |
| **POST** | `/api/v1/payments/webhook` | Public (Signed)| `PaymentWebhookController@handle`| Stripe cryptographically signed webhook |
| **GET** | `/api/v1/notifications` | Authenticated | `NotificationController@index` | User notifications list |
| **PATCH**| `/api/v1/notifications/read-all`| Authenticated| `NotificationController@markAllRead`| Mark all notifications as read |
| **GET** | `/api/health` | Public | Inline Controller | Infrastructure health check |

---

## 3. Web Client Backward Compatibility Layer

To guarantee **zero regressions** on the existing Vue 3 client, the following routing aliases are maintained:

```text
/api/public/settings         ──►  PublicController@settings
/api/public/services         ──►  PublicController@services
/api/public/barbers          ──►  PublicController@barbers
/api/public/available-slots  ──►  PublicController@availableSlots
/api/customer/bookings       ──►  CustomerController@bookings
/api/customer/profile        ──►  CustomerController@profile
/api/customer/my-codes       ──►  CustomerController@myCodes
/api/barber/dashboard        ──►  BarberController@dashboard
/api/barber/schedule         ──►  BarberController@schedule
/api/admin/*                 ──►  AdminController & SuperAdminController
```

---

## 4. Web Client Route Architecture (`barbing-saloon-web`)

### 4.1 Route Map
- **Public**:
  - `/` (Home / Hero / Flagship showcase)
  - `/services` (Service catalog & pricing)
  - `/barbers` (Master barbers & chair availability)
  - `/gallery` (Lookbook & styling portfolio)
  - `/about`, `/contact`, `/terms`, `/privacy`
- **Authentication**:
  - `/customer/login`, `/barber/login`, `/admin/login`
  - `/register`
  - `/forgot-password`, `/reset-password`
- **Customer Portal** (`requiresAuth: true`, `roles: ['customer']`):
  - `/customer/dashboard`
  - `/customer/book` (Booking stepper)
  - `/customer/appointments`
  - `/customer/my-codes`
  - `/customer/profile`
  - `/customer/notifications`
- **Barber Portal** (`requiresAuth: true`, `roles: ['barber']`):
  - `/barber/dashboard` (Live chair management)
  - `/barber/appointments`
  - `/barber/schedule`
  - `/barber/walk-in`
  - `/barber/profile`
- **Admin CMS** (`requiresAuth: true`, `roles: ['admin', 'super_admin']`):
  - `/admin/dashboard`
  - `/admin/barbers`, `/admin/customers`, `/admin/appointments`
  - `/admin/working-hours`, `/admin/verifications`
  - `/admin/services`, `/admin/gallery`, `/admin/testimonials`, `/admin/blog`
  - `/admin/settings`, `/admin/theme-studio`, `/admin/logs`

### 4.2 Context-Aware 401 Interceptor
In `src/core/api/axios.js`:
```javascript
client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      const auth = useAuthStore();
      auth.clearAuth();

      const path = window.location.pathname;
      if (path.startsWith('/admin')) {
        window.location.href = '/admin/login';
      } else if (path.startsWith('/barber')) {
        window.location.href = '/barber/login';
      } else {
        window.location.href = '/customer/login';
      }
    }
    return Promise.reject(error);
  }
);
```

---

## 5. Mobile Client Navigation Architecture (`candycutz-mobile-app`)

### 5.1 Root Navigation Switch (`app/_layout.tsx`)
```typescript
export default function RootLayout() {
  const { user, token, isLoading } = useAuthStore();

  if (isLoading) {
    return <SplashScreen />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      {!token ? (
        <Stack.Screen name="(auth)" />
      ) : user?.role === 'barber' ? (
        <Stack.Screen name="(barber)" />
      ) : (
        <Stack.Screen name="(customer)" />
      )}
    </Stack>
  );
}
```

### 5.2 Customer Tabs (`(customer)/(tabs)/_layout.tsx`)
1. **Home (`index.tsx`)**: Keffi flagship status, banner promotions, quick-book button.
2. **Explore (`services.tsx`)**: Service cards, pricing, category tabs.
3. **Appointments (`appointments.tsx`)**: Upcoming and past visits, verification QR codes.
4. **Profile (`profile.tsx`)**: @username display, Nigerian phone, notification preferences, dark mode toggle.

### 5.3 Barber Tabs (`(barber)/(tabs)/_layout.tsx`)
1. **Dashboard (`index.tsx`)**: Live chair status toggle (Free / Busy / Break / Offline), active haircut timer.
2. **Queue (`appointments.tsx`)**: Today's appointment queue with status action buttons (Check In, Start Cut, No-Show).
3. **Schedule (`schedule.tsx`)**: 7-day working hours editor with start/end sliders and day-off toggles.
4. **Profile (`profile.tsx`)**: Barber profile, bio, specialties, and chair assignment.
5. **Walk-In Modal (`walkin.tsx`)**: Rapid guest entry bypassing full customer registration.

---

## 6. CORS & Host Resolution Contracts

### 6.1 `config/cors.php`
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter([
        env('APP_URL'),
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5174',
        'http://localhost:3000',
        'https://candycutz.com',
        'https://admin.candycutz.com',
    ]),
    'allowed_origins_patterns' => [
        '#^http://(localhost|127\.0\.0\.1|10\.\d+\.\d+\.\d+|192\.168\.\d+\.\d+)(:\d+)?$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Idempotency-Key'],
    'max_age' => 86400,
    'supports_credentials' => true,
];
```
- Raw wildcard `header('Access-Control-Allow-Origin: *')` is completely removed from `public/index.php`.
