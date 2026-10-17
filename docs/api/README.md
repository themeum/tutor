# Tutor LMS REST API Documentation

This directory contains the complete, interactive API documentation and executable request collection for the **Tutor LMS** WordPress plugin, built with [Bruno](https://www.usebruno.com/).

---

## Getting Started

### 1. Open in Bruno GUI

1. Download and install [Bruno](https://www.usebruno.com/downloads).
2. Open Bruno, click **"Open Collection"**, and select the `wp-content/plugins/tutor/docs/api` directory.

### 2. Configure Environment & Authentication

Tutor LMS REST API uses **JWT authentication** (since 4.2.0):

1. In WordPress admin, go to **Tutor LMS → Settings → REST API**.
2. Generate an API Key & Secret with the desired permission (`Read` in Free; Pro also offers `Write`, `Delete`, `Read/Write`, and `All`).
3. Set up your local environment:
   ```bash
   cp docs/api/environments/local.bru.example docs/api/environments/local.bru
   ```
4. In Bruno, select the **local** environment and set:
   - `base_url` — e.g. `http://yoursite.local/wp-json/tutor/v1`
   - `api_key` / `api_secret` — from Tutor settings
   - `username` / `password` — WordPress user credentials
5. Run **Authentication → Login**. It stores `access_token` and `refresh_token` in the environment.
6. All other authenticated requests send `Authorization: Bearer {{access_token}}`.

`local.bru` is gitignored so credentials are not committed. For production, configure `environments/production.bru`.

### Auth flow

```
Login only:
  Tutor-Api-Key + Tutor-Api-Secret + username/password
        │
        ▼
  POST /auth/login  →  access_token + refresh_token
        │
        ▼
Other Tutor REST routes (when authenticated):
  Authorization: Bearer <access_token>  (or Tutor-User-Token header)
        │
        ├── POST /auth/refresh  (body: refresh_token) when access expires
        └── POST /auth/logout   (invalidate refresh token(s))
```

**Auth clarification**

| Route                       | Credentials                                                                                                                                     |
| :-------------------------- | :---------------------------------------------------------------------------------------------------------------------------------------------- |
| `POST /auth/login`          | `Tutor-Api-Key` + `Tutor-Api-Secret` headers + username/password body                                                                           |
| All other Tutor REST routes | Bearer JWT only (`Authorization: Bearer …` or `Tutor-User-Token`). Permission is taken from the API key id (`kid`) bound into the access token. |
| `POST /auth/refresh`        | Refresh token in body (no API key / Bearer required)                                                                                            |
| `POST /auth/logout`         | Optional Bearer and/or `refresh_token` / `all` in body                                                                                          |

---

## Collection Structure

- **`environments/`**: `local.bru.example` and `production.bru`.
- **`Authentication/`**: Login, refresh, and logout.
- **`Courses/`**: Course listings, details, curriculum trees, announcements, ratings.
- **`Curriculum/`**: Topics and lessons (read).
- **`Quizzes/`**: Quizzes, questions/answers, attempt details.
- **`Instructors/`**: Author/instructor profiles.
- **`Ecommerce & Webhooks/`**: Payment gateway webhooks (public).

---

## Running Requests via CLI

```bash
# Run against the local environment
npx @usebruno/cli run docs/api --env local

# Run against production
npx @usebruno/cli run docs/api --env production

# Run a specific folder (e.g., Courses)
npx @usebruno/cli run docs/api/Courses --env local
```

Run **Authentication → Login** first (or ensure `access_token` is set) before authenticated folders.

---

## API Response Format

Successful Tutor REST responses use an operation-specific `code` (not a generic `"success"`):

```json
{
  "code": "tutor_read_course",
  "message": "Human readable message",
  "data": {}
}
```

Examples of operation codes: `tutor_read_course`, `tutor_read_rating`, `tutor_read_quiz`, `tutor_auth_login`, `tutor_auth_refresh`, `tutor_auth_logout`.

### Error shape (`WP_Error`)

HTTP status ≥ 400 responses are WordPress `WP_Error` objects. The REST API serializes them as:

```json
{
  "code": "tutor_read_course",
  "message": "Course not found",
  "data": {
    "status": 404
  }
}
```

When the controller passes extra error details, they appear under `data.details`:

```json
{
  "code": "tutor_validation_error",
  "message": "Invalid input",
  "data": {
    "status": 400,
    "details": { "field": "…" }
  }
}
```

WordPress may also return core codes such as `rest_missing_callback_param` (missing required args) with `data.status` and `data.params`.

### HTTP status codes

- `200 OK`: Request succeeded (Tutor core read/auth routes).
- `201 Created`: Not used by Tutor LMS **core** REST routes today (core is mostly `GET` plus auth). Pro write endpoints may return `201` where applicable.
- `400 Bad Request`: Validation / missing required parameters.
- `401 Unauthorized` / `403 Forbidden`: Missing/invalid JWT, revoked API key, or insufficient permission.
- `404 Not Found`: Resource does not exist for the provided ID.

### Ecommerce webhook exception

`POST|GET|… /ecommerce-webhook/{payment_method}` does **not** use the Tutor JSON envelope. It is a public gateway callback: empty `payment_method` logs and exits; otherwise the gateway driver verifies the signature and may redirect. Prefer treating `payment_method` as effectively required.
