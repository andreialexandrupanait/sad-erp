# API Documentation

## Overview

The ERP API provides programmatic access to core business data. All API routes are prefixed with `/api/` and require authentication.

## Authentication

API routes use session-based authentication (web middleware). Ensure you are authenticated before making API requests.

For programmatic access, Laravel Sanctum can be configured for token-based authentication.

## Base URL

```
https://your-domain.com/api
```

## Endpoints

### Tasks API

#### Get Tasks by Status

Retrieve tasks filtered by status ID.

```
GET /api/tasks/status/{status}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | integer | Yes | Status ID from settings_options |

**Response:**
```json
{
  "tasks": [
    {
      "id": 1,
      "task_id": 1,
      "task_name": "Task Name",
      "status_id": 1,
      "status_name": "To Do",
      "status_color": "#3B82F6",
      "list_id": 1,
      "list_name": "Project List",
      "assignee_name": "John Doe",
      "priority_name": "High",
      "priority_color": "#EF4444",
      "due_date": "2025-12-01",
      "position": 1,
      "updated_at": "2025-11-27T10:00:00Z"
    }
  ],
  "total": 10,
  "page": 1,
  "per_page": 50,
  "has_more": false
}
```

#### Get Status Counts

Retrieve task counts grouped by status.

```
GET /api/tasks/status-counts
```

**Response:**
```json
{
  "1": 5,
  "2": 10,
  "3": 3
}
```

Keys are status IDs, values are task counts.

---

## Internal Routes (Web)

The following routes are available via the web interface and return HTML views or JSON responses.

### Clients

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/clients` | List all clients |
| GET | `/clients/create` | Show create form |
| POST | `/clients` | Create new client |
| GET | `/clients/{client}` | Show client details |
| GET | `/clients/{client}/edit` | Show edit form |
| PUT/PATCH | `/clients/{client}` | Update client |
| DELETE | `/clients/{client}` | Delete client |
| PATCH | `/clients/{client}/status` | Update client status (AJAX) |
| PATCH | `/clients/{client}/reorder` | Update client order (AJAX) |

### Domains

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/domains` | List all domains |
| POST | `/domains` | Create new domain |
| GET | `/domains/{domain}` | Show domain details |
| PUT/PATCH | `/domains/{domain}` | Update domain |
| DELETE | `/domains/{domain}` | Delete domain |

### Subscriptions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/subscriptions` | List all subscriptions |
| POST | `/subscriptions` | Create new subscription |
| GET | `/subscriptions/{subscription}` | Show subscription details |
| PUT/PATCH | `/subscriptions/{subscription}` | Update subscription |
| DELETE | `/subscriptions/{subscription}` | Delete subscription |
| PATCH | `/subscriptions/{subscription}/status` | Update status |
| POST | `/subscriptions/{subscription}/renew` | Renew subscription |

### Credentials

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/credentials` | List all credentials |
| POST | `/credentials` | Create new credential |
| GET | `/credentials/{credential}` | Show credential details |
| PUT/PATCH | `/credentials/{credential}` | Update credential |
| DELETE | `/credentials/{credential}` | Delete credential |
| POST | `/credentials/{credential}/reveal-password` | Reveal password (throttled) |

### Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks` | Livewire task list |
| POST | `/tasks` | Create new task |
| GET | `/tasks/{task}` | Show task details |
| PUT/PATCH | `/tasks/{task}` | Update task |
| DELETE | `/tasks/{task}` | Delete task |
| PATCH | `/tasks/{task}/status` | Update task status |
| POST | `/tasks/{task}/duplicate` | Duplicate task |
| GET | `/tasks/{task}/details` | Get task details (AJAX) |

### Financial

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/financial` | Financial dashboard |
| GET | `/financial/revenues` | List revenues |
| POST | `/financial/revenues` | Create revenue |
| GET | `/financial/expenses` | List expenses |
| POST | `/financial/expenses` | Create expense |
| GET | `/financial/cashflow` | Cashflow analysis |
| GET | `/financial/history` | Historical data |

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "Resource not found."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message for this field."
    ]
  }
}
```

### 429 Too Many Requests
```json
{
  "message": "Too Many Attempts."
}
```

### 500 Server Error
```json
{
  "message": "Server Error"
}
```

---

## Rate Limiting

Certain endpoints have rate limiting applied:

| Endpoint | Limit |
|----------|-------|
| `/credentials/{id}/reveal-password` | 6 requests per minute |
| `/internal-accounts/{id}/reveal-password` | 6 requests per minute |

---

## Request Headers

For JSON responses, include:
```
Accept: application/json
Content-Type: application/json
```

For CSRF protection on POST/PUT/PATCH/DELETE:
```
X-CSRF-TOKEN: {csrf_token}
```

Or include `_token` in the request body.

---

## Pagination

List endpoints return paginated results:

```json
{
  "data": [...],
  "current_page": 1,
  "first_page_url": "...",
  "from": 1,
  "last_page": 5,
  "last_page_url": "...",
  "links": [...],
  "next_page_url": "...",
  "path": "...",
  "per_page": 50,
  "prev_page_url": null,
  "to": 50,
  "total": 234
}
```

Query parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: varies by endpoint)
