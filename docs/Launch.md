# Candycutz — Mobile Store Release & Launch Plan

## 1. Overview & Release Tracks
Candycutz mobile applications are released across both Apple App Store (iOS) and Google Play Store (Android) using **EAS (Expo Application Services)**.

```
Development Build ──► Internal Testing ──► Closed Testing / TestFlight ──► Production Release
```

---

## 2. EAS Build Configuration (`eas.json`)

Both `candycutz-customer-app` and `candycutz-barber-app` utilize standardized `eas.json` build profiles:
```json
{
  "cli": {
    "version": ">= 10.0.0"
  },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal"
    },
    "preview": {
      "distribution": "internal",
      "channel": "preview"
    },
    "production": {
      "channel": "production",
      "autoIncrement": true,
      "android": {
        "buildType": "app-bundle"
      },
      "ios": {
        "simulator": false
      }
    }
  },
  "submit": {
    "production": {
      "android": {
        "serviceAccountKeyPath": "./google-service-account.json",
        "track": "production"
      },
      "ios": {
        "appleId": "concierge@candycutz.com",
        "ascAppId": "6501234567"
      }
    }
  }
}
```

---

## 3. Store Safety & Keystore Management

### 3.1 Android Signing
- **Production Format**: Android App Bundle (`.aab`) required for Google Play.
- **Keystore**: Managed through EAS Credentials or hardware-backed signing keys.
- **Direct APK Distribution**: Internal testing builds must be signed with valid upload keys and distributed exclusively through controlled channels.

### 3.2 Apple App Store Guidelines Compliance
- **In-App Account Deactivation**: Mandated by Apple Guideline 5.1.1(v). Customers can initiate soft account deactivation directly within the Profile screen.
- **Sign in with Apple**: Mandatory since third-party social auth (Google) is offered (Apple Guideline 4.8).
- **Physical Services Exemption**: Grooming appointments are physical in-person / home services, exempting them from Apple's 30% In-App Purchase (IAP) commission; Stripe credit card checkout is fully compliant.

---

## 4. Store Metadata & Brand Assets

### 4.1 Visual Brand Assets Checklist
- **App Icon**: 1024x1024 PNG, gold monogram `#C6A15B` on deep obsidian `#0B0B0B`, no transparency.
- **Adaptive Icon (Android)**: 432x432 foreground with matching `#0B0B0B` background.
- **Splash Screen**: Native splash with centered logo and subtle brand shimmer.
- **Store Screenshots**:
  1. *Hero Splash*: "Your style. Your barber. Your time."
  2. *Discovery*: "Discover master stylists in Keffi."
  3. *Booking Stepper*: "Seamless In-Shop & Home Service scheduling."
  4. *Luxury Grooming*: "Real-time queue tracking and instant confirmations."

---

## 5. Go-Live Operational Readiness Checklist

- [ ] Production database migrated and seeded with authentic Keffi operational data.
- [ ] Stripe Live API keys and signed webhook secret active.
- [ ] Brevo transactional email SMTP relay verified with SPF, DKIM, and DMARC DNS records.
- [ ] Redis queue worker running and monitored via systemd.
- [ ] Android AAB uploaded to Google Play Internal Testing track.
- [ ] iOS build uploaded to TestFlight for staff device verification.
- [ ] Physical salon staff in Angwan Kare trained on the Barber Mobile App and walk-in flows.
- [ ] Live monitoring and error tracking (Sentry / Bugsnag) configured.
