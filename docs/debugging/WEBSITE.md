# Candycutz — Website Debugging Runbook (`barbing-saloon-web`)

## 1. Problem: 401 Unauthorized Forces Redirect to Customer Login

### What it looks like:
An Admin or Barber is working in their portal (`/admin/*` or `/barber/*`). Suddenly, a full page reload occurs and they land on `/customer/login`.

### Root Cause:
`src/core/api/axios.js` contained a blind redirect:
```javascript
window.location.href = '/customer/login';
```
Whenever an access token expired or failed verification, the user was thrown to the customer login screen.

### How to Fix:
Ensure the context-aware 401 interceptor is active in `src/core/api/axios.js`:
```javascript
const path = window.location.pathname;
if (path.startsWith('/admin')) {
  window.location.href = '/admin/login';
} else if (path.startsWith('/barber')) {
  window.location.href = '/barber/login';
} else {
  window.location.href = '/customer/login';
}
```

### Verification:
Log into `/admin/login`, manually delete `candycutz_auth_token` from browser Local Storage, and refresh. The page should redirect to `/admin/login`, not `/customer/login`.

---

## 2. Problem: CORS Violation in Browser Console

### What it looks like:
In Chrome DevTools Console:
> `Access to XMLHttpRequest at 'http://localhost:8000/api/...' from origin 'http://localhost:5174' has been blocked by CORS policy: The value of the 'Access-Control-Allow-Origin' header in the response must not be the wildcard '*' when the request's credentials mode is 'include'.`

### Root Cause:
Raw `header('Access-Control-Allow-Origin: *')` was emitted from PHP while the request sent cookies or credentials.

### How to Fix:
1. Ensure no manual CORS headers exist in `public/index.php`.
2. Update `config/cors.php` to include the active port/domain in `allowed_origins` and dynamic pattern matching:
   ```php
   'allowed_origins_patterns' => ['#^http://(localhost|127\.0\.0\.1)(:\d+)?$#'],
   ```
3. Clear Laravel config cache:
   ```bash
   docker compose exec app php artisan config:clear
   ```

---

## 3. Problem: Stale Assets / Changes Not Visible After Deployment

### What it looks like:
You deployed new code, but the browser continues showing old UI styles or components.

### Root Cause:
Vite production assets are fingerprinted (`index-XXXXXX.js`), but the browser or Nginx has cached the old `index.html`.

### How to Fix:
1. In `nginx.conf`, ensure `index.html` has zero caching:
   ```nginx
   location = /index.html {
       add_header Cache-Control "no-store, no-cache, must-revalidate";
   }
   ```
2. Rebuild the frontend:
   ```bash
   cd barbing-saloon-web && npm run build
   ```
3. Hard refresh the browser (`Ctrl + F5` or `Cmd + Shift + R`).
