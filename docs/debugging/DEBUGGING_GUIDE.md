# Candycutz — Master Debugging & Troubleshooting Guide

## 1. Primary Debugging Philosophy

When troubleshooting an issue in Candycutz:
1. **Never guess**: Inspect logs first.
2. **Isolate the tier**: Determine whether the issue is Client (Vue 3 / Expo), Gateway (Nginx / CORS), Framework (Laravel / Sanctum), or Database (MySQL / Locks).
3. **Follow the Request-Response lifecycle**:
   `Client -> Nginx (443) -> FastCGI -> Laravel Middleware -> FormRequest -> Controller -> Service (DB Transaction) -> API Resource -> JSON Response`

---

## 2. Global Triage Flowchart

```text
Problem Observed
      │
      ▼
Is the API reachable?
 ├── NO  ──► Check Nginx & App containers: `docker compose ps`
 │           Inspect Nginx logs: `docker compose logs nginx`
 └── YES ──► Does it return a standard JSON error envelope?
              ├── YES ──► Check error code in `docs/api/API_ERROR_CODES.md`
              └── NO  ──► Check PHP fatal error in `storage/logs/laravel.log`
```

---

## 3. Quick Diagnosis Commands

```bash
# 1. Check health of all running containers
docker compose ps

# 2. Check live Laravel logs
docker compose exec app tail -n 100 storage/logs/laravel.log

# 3. Check Nginx access and error logs
docker compose logs -f nginx

# 4. Check Queue worker activity
docker compose logs -f worker

# 5. Check MySQL active processlist (check for stuck locks)
docker compose exec db mysql -u candycutz -p -e "SHOW FULL PROCESSLIST;"

# 6. Test database connectivity directly
docker compose exec app php artisan db:monitor
```

---

## 4. Module Troubleshooting Runbooks

For specific domain runbooks, consult:
- **Website Client**: [`docs/debugging/WEBSITE.md`](file:///c:/xampp/htdocs/1/candycutz/docs/debugging/WEBSITE.md)
- **Mobile Application**: [`docs/debugging/MOBILE.md`](file:///c:/xampp/htdocs/1/candycutz/docs/debugging/MOBILE.md)
- **Authentication & Sessions**: [`docs/debugging/AUTHENTICATION.md`](file:///c:/xampp/htdocs/1/candycutz/docs/debugging/AUTHENTICATION.md)
- **Payments & Stripe**: [`docs/debugging/PAYMENTS.md`](file:///c:/xampp/htdocs/1/candycutz/docs/debugging/PAYMENTS.md)
- **Transactional Notifications**: [`docs/debugging/NOTIFICATIONS.md`](file:///c:/xampp/htdocs/1/candycutz/docs/debugging/NOTIFICATIONS.md)
