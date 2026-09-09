# Candycutz — API Error Codes & Diagnostics Catalog

## 1. Central Error Schema

Every error emitted by `/api/v1/*` conforms to this standardized JSON format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE_CONSTANT",
    "message": "Human-readable description suitable for display or logs.",
    "details": {
      "field": ["Specific validation error message."]
    }
  }
}
```

---

## 2. Standard Error Code Registry

| Error Code | HTTP Status | Description | Client Action / Troubleshooting |
|---|---|---|---|
| `UNAUTHENTICATED` | `401` | Missing or invalid Bearer token | Redirect to respective portal login |
| `SESSION_EXPIRED` | `401` | Token expired or deleted | Clear local storage and re-authenticate |
| `INVALID_CREDENTIALS` | `401` | Incorrect email/username or password | Prompt user to verify credentials |
| `ACCOUNT_SUSPENDED` | `403` | User account deactivated or suspended | Display contact support modal |
| `FORBIDDEN_ROLE` | `403` | User lacks required role for resource | Display 403 screen or redirect to home |
| `VALIDATION_FAILED` | `422` | Form payload failed validation rules | Highlight specific fields in `details` |
| `RESOURCE_NOT_FOUND` | `404` | Requested record or route does not exist | Verify endpoint URL and entity ID |
| `BOOKING_SLOT_UNAVAILABLE` | `422` | Conflicting appointment already reserved | Refresh available slots; prompt client |
| `BARBER_UNAVAILABLE` | `422` | Barber is on leave, blocked, or off-duty | Prompt client to select another stylist |
| `INVALID_TRANSITION` | `422` | Illegal appointment status transition | E.g., cannot complete cancelled cut |
| `SERVICE_ZONE_OUT_OF_BOUNDS` | `422` | Home service address outside Keffi zone | Display delivery zone map |
| `PAYMENT_INTENT_FAILED` | `402` | Stripe payment gateway rejected charge | Prompt user for alternative payment |
| `WEBHOOK_SIGNATURE_INVALID`| `400` | Stripe webhook signature mismatch | Verify `STRIPE_WEBHOOK_SECRET` in `.env` |
| `IDEMPOTENCY_COLLISION` | `409` | Request with same key currently executing | Wait and poll for original request |
| `RATE_LIMIT_EXCEEDED` | `429` | Too many requests sent in time window | Retry after `Retry-After` header period |
| `SERVER_ERROR` | `500` | Uncaught server exception | Inspect `storage/logs/laravel.log` |
| `MAINTENANCE_MODE` | `503` | Platform temporarily down for upgrade | Display maintenance screen |

---

## 3. Frontend Error Handling Integration

### 3.1 Mobile Client Handling (`src/api/client.ts`)
```typescript
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<{ error: { code: string; message: string } }>) => {
    const errorData = error.response?.data?.error;
    const errorCode = errorData?.code;

    switch (errorCode) {
      case 'BOOKING_SLOT_UNAVAILABLE':
        Alert.alert('Slot Taken', 'Another customer just booked this slot. Please choose another time.');
        break;
      case 'ACCOUNT_SUSPENDED':
        Alert.alert('Account Deactivated', 'Your account is inactive. Please contact CandyCutz support.');
        break;
      case 'UNAUTHENTICATED':
      case 'SESSION_EXPIRED':
        tokenStorage.remove();
        useAuthStore.getState().logout();
        break;
      default:
        Alert.alert('Error', errorData?.message || 'A network error occurred. Please try again.');
    }

    return Promise.reject(error);
  }
);
```

### 3.2 Web Client Handling (`src/core/api/axios.js`)
```javascript
client.interceptors.response.use(
  (response) => response,
  (error) => {
    const errorData = error.response?.data?.error;
    const code = errorData?.code;
    const message = errorData?.message || 'An error occurred.';

    if (error.response?.status === 401) {
      const auth = useAuthStore();
      auth.clearAuth();
      const path = window.location.pathname;
      if (path.startsWith('/admin')) window.location.href = '/admin/login';
      else if (path.startsWith('/barber')) window.location.href = '/barber/login';
      else window.location.href = '/customer/login';
      return Promise.reject(error);
    }

    toast.error(message);
    return Promise.reject(error);
  }
);
```
