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
2. Generate an API Key & Secret with the desired permission (`Read`, `Write`, or `All`).
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
Tutor-Api-Key + Tutor-Api-Secret + username/password
        │
        ▼
  POST /auth/login  →  access_token + refresh_token
        │
        ▼
  Authorization: Bearer <access_token>  (or Tutor-User-Token header)
        │
        ├── POST /auth/refresh  (body: refresh_token) when access expires
        └── POST /auth/logout   (invalidate refresh token(s))
```

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

```json
{
  "code": "success",
  "message": "Human readable message",
  "data": {}
}
```

### Common Status Codes

- `200 OK`: Request succeeded.
- `401 Unauthorized` / `403 Forbidden`: Missing/invalid JWT, revoked API key, or insufficient permission.
- `404 Not Found`: Resource does not exist for the provided ID.
