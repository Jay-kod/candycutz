# Candycutz — Authentication Forensic Debugging & Recovery Runbook

## 1. Sanctum Token Validation & Diagnostic Commands

### 1.1 Inspect Active Tokens for a User
To check all active tokens and device names for a specific user:
```bash
docker compose exec app php artisan tinker --execute="
\$user = App\Models\User::where('email', 'james@candycutz.com')->first();
foreach (\$user->tokens as \$token) {
    echo \"ID: {\$token->id} | Name: {\$token->name} | Created: {\$token->created_at} | Last Used: {\$token->last_used_at}\n\";
}
"
```

### 1.2 Revoke Only Mobile Tokens (Leaving Web Active)
```bash
docker compose exec app php artisan tinker --execute="
\$user = App\Models\User::where('email', 'james@candycutz.com')->first();
\$user->tokens()->where('name', 'mobile-client')->delete();
echo 'Mobile tokens revoked.\n';
"
```

---

## 2. Diagnosing Google / Apple ID Token Failures

### 2.1 Decoding ID Token Manually to Inspect Claims
When debugging social auth failures, inspect the decoded claims of the token received from the client:
```bash
docker compose exec app php artisan tinker --execute="
\$jwt = 'eyJhbGciOiJSUzI1NiIs...';
[\$header, \$payload, \$signature] = explode('.', \$jwt);
print_r(json_decode(base64_decode(\$payload), true));
"
```
Verify the following claims:
- `iss`: Must be `https://accounts.google.com` (for Google) or `https://appleid.apple.com` (for Apple).
- `aud`: Must match your `GOOGLE_CLIENT_ID` or `APPLE_CLIENT_ID`.
- `exp`: Timestamp must be in the future.

---

## 3. Resolving Multi-Identifier Collisions

If a customer cannot log in with their phone number:
1. Check how the phone number was formatted:
   ```bash
   docker compose exec app php artisan tinker --execute="
   \$phone = '08012345678';
   \$user = App\Models\User::where('phone', \$phone)->orWhere('phone', '+234' . substr(\$phone, 1))->first();
   echo \$user ? 'Found: ' . \$user->email : 'Not found';
   "
   ```
2. The Nigerian phone regular expression:
   `/^(?:\+?234|0)(?:7[0-9]|8[0-9]|9[0-1])[0-9]{8}$/`
   Accepts standard Nigerian mobile prefixes (MTN, Airtel, Glo, 9mobile).
