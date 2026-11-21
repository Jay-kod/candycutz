# Exposing the Local App for Mobile Testing (Tunnels)

This guide documents how to temporarily expose the local Dockerized Laravel API (`http://localhost:8080`) to the public internet so you can test it on a physical smartphone (over cellular data) or share it with reviewers without opening router ports or deploying to a VPS.

---

## Tunnel Options

### Option A: Cloudflare Quick Tunnel (Recommended — No Account Needed)
A zero-setup, temporary tunnel provided by Cloudflare. The URL changes every time you launch it.

```bash
cloudflared tunnel --url http://localhost:8080
```
Output will include a public URL such as `https://<random-name>.trycloudflare.com`.

### Option B: ngrok (Free Account Required)
Provides a stable tunnel URL if reserved, or random URL on the free tier.

```bash
ngrok http 8080
```
Output will display a public forwarding URL such as `https://<random-id>.ngrok-free.app`.

---

## Step-by-Step Instructions

1. **Start the local Docker stack**:
   ```bash
   docker compose up -d
   ```
2. **Start the tunnel** in a second terminal window (using Option A or B above).
3. **Update `APP_URL` in `.env.docker`**:
   Copy the `https://...` tunnel URL into `.env.docker`:
   ```env
   APP_URL=https://<your-tunnel-url>
   ```
   Then reload the app container so Laravel's cached config reflects the new URL:
   ```bash
   docker compose up -d --force-recreate app
   ```
   *(Note: This recreation is required because `config:cache` in `start.sh` bakes `APP_URL` into storage at startup).*
4. **Test on mobile**:
   - Open your phone browser or mobile app over mobile data (not WiFi) to verify external routing.
   - Test health check: `https://<your-tunnel-url>/up` (should return 200 OK).
   - Test API endpoints: `https://<your-tunnel-url>/api/v1/services`.
5. **Tear down when finished**:
   - Press `Ctrl+C` in the tunnel terminal to stop the tunnel. The URL immediately dies.
   - Reset `APP_URL` in `.env.docker` back to `http://localhost:8080` and restart:
     ```bash
     docker compose up -d --force-recreate app
     ```

---

## Security Requirements

- **Keep `APP_DEBUG=false`**: Never expose an application with debug mode enabled (`APP_DEBUG=true`) to the public internet. Debug screens can leak database credentials, environment keys, and full stack traces.
- **Use throwaway test data only**: Never seed or import real customer PII or production payment credentials while running through a public tunnel.
- **Do not leave tunnels unattended**: Quick tunnel URLs are public and unauthenticated by default. Anyone with the URL can make requests against your local database. Stop the tunnel as soon as testing concludes.
- **Keep rate limiting & validation enabled**: All Sanctum, API gate, and throttle middleware must remain active during testing.
