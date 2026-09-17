# CandyCutz — Agent Rules

**Authoritative architecture:** [`ARCHITECTURE.md`](./ARCHITECTURE.md) in this repository root.

All autonomous agents and engineers working on this codebase must read `ARCHITECTURE.md` in full before their first action in every session. That document defines the target state; where the codebase disagrees with it, the codebase is wrong.

## Core Rules

1. **Never rewrite working code to match stylistic preference.** Preserve correct business logic (BookingService locking, PaymentService idempotency, PaymentWebhookApiController signature verification, Base*.vue components, mobile `src/`).
2. **Never delete code without a test capturing its behaviour first.**
3. **One phase per session.** Each phase ends with a working, deployable system.
4. **Ask before assuming business rules.** Do not infer policy from archived documentation.
5. **Security fixes are never deferred.** Report immediately.
6. **Report honestly.** If an exit criterion does not pass, say so and say why.
7. **No AI-related references** in code, UI, or commit messages.
8. **No raw secrets** in code. All credentials via environment variables.
9. **One backend, one database, one mobile app.** See `ARCHITECTURE.md` §1.
10. **`/api/v1/*` is the only API mount point.** See `ARCHITECTURE.md` §2.

## Forbidden Practices

See `ARCHITECTURE.md` §5 (Security) and the original archived `docs/archive/AGENTS.md` for the full forbidden-practices list. Key items:

- Do not create a second backend, database, or mobile project.
- Do not wipe all user tokens on login.
- Do not commit un-hashed passwords or live API secrets.
- Do not leave scratch scripts in application roots.
