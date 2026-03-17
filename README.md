# Money Tracker API

A secure RESTful API built with **PHP Laravel 12** that allows users to manage multiple wallets, track income and expense transactions, and share wallets with other users.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation & Setup](#installation--setup)
- [Security Features](#security-features)
- [Database Design](#database-design)
- [API Endpoints](#api-endpoints)
- [Design Decisions](#design-decisions)

---

## Requirements

- PHP >= 8.3
- Composer
- MySQL 8.0+
- Laravel 12

---

## Installation & Setup

### 1. Clone the repository

```bash
git clone https://github.com/Native-254/money-tracker.git
cd money-tracker
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to point to your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=money_tracker
DB_USERNAME=laravel
DB_PASSWORD=your_password
```

### 4. Install Sanctum API scaffolding

```bash
php artisan install:api
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Start the development server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

---

## Security Features

### Authentication — Laravel Sanctum
All endpoints (except register and login) are protected by Sanctum token authentication. Every request to a protected route must include a Bearer token in the Authorization header:

```
Authorization: Bearer your_token_here
```

Tokens are issued on registration and login, and invalidated on logout.

### Rate Limiting
The login endpoint is limited to **5 requests per minute per IP address** to prevent brute force attacks. Exceeding this limit returns a `429 Too Many Requests` response.

### SQL Injection Protection
All database queries use Laravel's Eloquent ORM with parameterized prepared statements. No raw SQL string concatenation is used anywhere in the codebase.

### Mass Assignment Protection
All models declare explicit `$fillable` arrays. Only whitelisted fields can be set through mass assignment, preventing attackers from injecting unauthorized fields.

### XSS Protection
Free-text fields (`name`, `description`) are sanitized using `strip_tags()` before validation and storage, removing any HTML or script tags from user input.

### Cascading Deletes
Foreign key constraints with `onDelete('cascade')` ensure that deleting a user removes all their wallets, and deleting a wallet removes all its transactions — no orphaned records.

### Database Indexing
A composite index on `transactions(wallet_id, type)` optimizes balance calculation queries which always filter by both columns simultaneously.

### Authorization
Every protected endpoint verifies that the authenticated user owns or is a member of the resource being accessed. Users cannot read or modify other users' data.

---

## Database Design

```
users
  id, name, email, password (nullable), remember_token,
  email_verified_at, created_at, updated_at

wallets
  id, user_id (FK → users.id CASCADE), name,
  created_at, updated_at

transactions
  id, wallet_id (FK → wallets.id CASCADE), type (income|expense),
  amount (decimal 15,2), description (nullable),
  created_at, updated_at

wallet_user  (pivot — shared wallets)
  id, wallet_id (FK → wallets.id CASCADE),
  user_id (FK → users.id CASCADE),
  role (owner|member), created_at, updated_at
  UNIQUE(wallet_id, user_id)

personal_access_tokens  (Sanctum)
  id, tokenable_type, tokenable_id, name, token,
  abilities, last_used_at, expires_at, created_at, updated_at
```

**Balances are never stored.** The balance of any wallet is always calculated dynamically by summing income transactions and subtracting expense transactions. This eliminates any risk of the balance falling out of sync.

---

## API Endpoints

All endpoints are prefixed with `/api`. The `Content-Type` header must be `application/json`.

Protected routes require: `Authorization: Bearer <token>`

---

### Authentication

#### Register

**`POST /api/register`** — Public

| Field | Type | Required | Notes |
|---|---|---|---|
| name | string | Yes | Max 255 chars. HTML tags stripped. |
| email | string | Yes | Must be unique |
| password | string | Yes | Min 8 characters |
| password_confirmation | string | Yes | Must match password |

**Response (201):**
```json
{
  "message": "Account created successfully.",
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

#### Login

**`POST /api/login`** — Public — Rate limited: 5/min per IP

| Field | Type | Required |
|---|---|---|
| email | string | Yes |
| password | string | Yes |

**Response (200):**
```json
{
  "message": "Login successful.",
  "token": "2|xyz789..."
}
```

---

#### Logout

**`POST /api/logout`** — Protected

Invalidates the current token. No request body required.

**Response (200):**
```json
{
  "message": "Logged out successfully."
}
```

---

### Users

#### View User Profile

**`GET /api/users/{user_id}`** — Protected

Returns all wallets belonging to the authenticated user, each wallet's balance, and the total balance across all wallets. Users can only view their own profile.

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallets": [
      { "id": 1, "name": "Personal Wallet", "balance": 39500.00 },
      { "id": 2, "name": "Business Wallet", "balance": 120000.00 }
    ],
    "total_balance": 159500.00
  }
}
```

---

### Wallets

#### Create a Wallet

**`POST /api/users/{user_id}/wallets`** — Protected

Users can only create wallets for themselves. The creator is automatically registered as the wallet owner in the shared wallet pivot table.

| Field | Type | Required | Notes |
|---|---|---|---|
| name | string | Yes | Max 255 chars |

**Response (201):**
```json
{
  "message": "Wallet created successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Personal Wallet",
    "balance": 0.00
  }
}
```

---

#### View a Wallet

**`GET /api/wallets/{wallet_id}`** — Protected

Returns the wallet's balance and full transaction history (newest first). Accessible by both the wallet owner and invited members.

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Personal Wallet",
    "user_id": 1,
    "balance": 39500.00,
    "transactions": [
      {
        "id": 4,
        "wallet_id": 1,
        "type": "expense",
        "amount": 3500.00,
        "description": "Groceries",
        "created_at": "2026-03-15T12:00:00.000000Z"
      },
      {
        "id": 1,
        "wallet_id": 1,
        "type": "income",
        "amount": 50000.00,
        "description": "Monthly salary",
        "created_at": "2026-03-15T10:00:00.000000Z"
      }
    ]
  }
}
```

---

#### Invite a Member to a Wallet

**`POST /api/wallets/{wallet_id}/invite`** — Protected

Only the wallet owner can invite other users. Invited members can view the wallet and add transactions.

| Field | Type | Required | Notes |
|---|---|---|---|
| email | string | Yes | Must belong to an existing user |

**Response (200):**
```json
{
  "message": "User added to wallet successfully."
}
```

---

### Transactions

#### Add a Transaction

**`POST /api/wallets/{wallet_id}/transactions`** — Protected

Both the wallet owner and invited members can add transactions. The `description` field is sanitized to strip HTML tags.

| Field | Type | Required | Notes |
|---|---|---|---|
| type | string | Yes | Must be `income` or `expense` |
| amount | decimal | Yes | Must be greater than 0 |
| description | string | No | Max 500 chars. HTML tags stripped. |

**Response (201):**
```json
{
  "message": "Transaction recorded successfully.",
  "data": {
    "id": 1,
    "wallet_id": 1,
    "type": "income",
    "amount": 50000.00,
    "description": "Monthly salary"
  },
  "wallet_balance": 50000.00
}
```

---

## Error Responses

| Status | Meaning |
|---|---|
| 401 | Unauthenticated — missing or invalid Bearer token |
| 403 | Unauthorized — authenticated but not allowed to access this resource |
| 404 | Resource not found |
| 409 | Conflict — e.g. user is already a wallet member |
| 422 | Validation failed — response includes field-level error messages |
| 429 | Too many requests — rate limit exceeded |

---

## Design Decisions

**Sanctum token authentication.** API tokens are issued on login and invalidated on logout. Every login generates a fresh token, replacing the previous one. This ensures stale tokens cannot be reused after a new login session begins.

**Dynamic balance calculation.** Balances are computed at query time rather than stored as a column. This keeps the data model simple and consistent — the balance is always a direct reflection of the actual transaction records and can never go out of sync.

**Shared wallets via pivot table.** The `wallet_user` pivot table supports many-to-many wallet membership with roles (`owner` or `member`). Owners can invite members; both can add transactions and view the wallet.

**Cascading deletes at the database level.** Referential integrity is enforced by MySQL foreign key constraints, not application code. This means data is cleaned up correctly even if records are deleted directly via a database client.

**Positive amounts only.** The `amount` field is always a positive decimal. The `type` column determines whether it adds to or subtracts from the balance. This makes queries, reporting, and auditing straightforward.

**XSS sanitization before validation.** `strip_tags()` runs before Laravel's validator, so the cleaned value is what gets validated and stored. This prevents script injection at the storage level rather than just at the display level.

---

## Real Time API Tests

### Transaction API
![Transaction API Test](https://github.com/user-attachments/assets/c9d8a893-2938-4a44-9bf4-89a4d5332dd6)

### User Profile API
![User Profile API Test](https://github.com/user-attachments/assets/c5487152-db7e-4d19-a25e-a3d2cf2f8a72)
