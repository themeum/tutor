# Tutor LMS REST API Documentation

This directory contains the complete, interactive API documentation and executable request collection for the **Tutor LMS** WordPress plugin, built with [Bruno](https://www.usebruno.com/).

---

## 🚀 Getting Started

### 1. Open in Bruno GUI
1. Download and install [Bruno](https://www.usebruno.com/downloads).
2. Open Bruno, click **"Open Collection"**, and select the `wp-content/plugins/tutor/docs/api` directory.

### 2. Configure Environment & Authentication
Tutor LMS REST API uses Basic Authentication with **API Keys** and **API Secrets**.

1. In your WordPress admin dashboard, navigate to **Tutor LMS > Settings > REST API / API**.
2. Generate an API Key & Secret with the desired permission level (`Read`, `Write`, or `All`).
3. Set up your local environment file:
   - Make a copy of `environments/local.bru.example` named `environments/local.bru`:
     ```bash
     cp docs/api/environments/local.bru.example docs/api/environments/local.bru
     ```
   - In Bruno, select the **local** environment from the top-right dropdown.
   - Configure `base_url`, `api_key`, and `api_secret` in the environment settings (or directly in `environments/local.bru`). Note that `local.bru` is gitignored so your credentials won't be committed.
4. For production environments, configure `environments/production.bru`.

---

## 📁 Collection Structure

The collection is organized into the following categories:

- **`environments/`**: Contains `local.bru.example` and `production.bru`.
- **`Authentication/`**: Overview of authentication mechanisms, key management, and headers.
- **`Courses/`**: Endpoints for querying course listings, course details, complete curriculum trees, announcements, and ratings/reviews.
- **`Curriculum/`**: Topic modules and lesson querying endpoints.
- **`Quizzes/`**: Quiz structures, questions, answers, and student attempt histories.
- **`Instructors/`**: Author/instructor profiles, bio, and associated course references.
- **`Ecommerce & Webhooks/`**: Payment gateway webhooks and processing handlers.

---

## 💻 Running Requests via CLI

You can execute this collection in terminal or CI/CD pipelines using the Bruno CLI:

```bash
# Run against the local environment
npx @usebruno/cli run docs/api --env local

# Run against production
npx @usebruno/cli run docs/api --env production

# Run a specific folder (e.g., Courses)
npx @usebruno/cli run docs/api/Courses --env local
```

---

## 📡 API Response Format

All Tutor LMS REST API endpoints return a standardized JSON envelope:

```json
{
  "code": "success",
  "message": "Human readable message",
  "data": { ... }
}
```

### Common Status Codes:
- `200 OK`: Request succeeded.
- `401 Unauthorized` / `403 Forbidden`: Missing, invalid, or insufficient API keys / user capabilities.
- `404 Not Found`: Resource (Course, Topic, Lesson, Quiz, or Instructor) does not exist for the provided ID.
