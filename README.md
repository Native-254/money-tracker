# Money Tracker API

A simple RESTful API built with **PHP Laravel** that allows users to manage multiple wallets and track income and expense transactions.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation & Setup](#installation--setup)
- [Database Design](#database-design)
- [API Endpoints](#api-endpoints)
- [Design Decisions](#design-decisions)
- [Suggested Commit History](#suggested-commit-history)

---

## Requirements

- PHP >= 8.1
- Composer
- MySQL or SQLite
- Laravel 10+

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
DB_USERNAME=root
DB_PASSWORD=
```

> **Tip:** For quick local testing, set `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Start the development server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

---

## Database Design

```
users
  id, name, email, created_at, updated_at

wallets
  id, user_id (FK → users.id), name, created_at, updated_at

transactions
  id, wallet_id (FK → wallets.id), type (income|expense),
  amount (decimal), description (nullable), created_at, updated_at
```

**Key design decision — balances are not stored.** The balance of any wallet is always calculated dynamically by summing its income transactions and subtracting its expense transactions. This approach eliminates the risk of the stored balance falling out of sync with the transaction history.

---

## API Endpoints

All endpoints are prefixed with `/api`. The `Content-Type` header should be `application/json`.

---

### Create a User

**`POST /api/users`**

| Field | Type   | Required | Description        |
|-------|--------|----------|--------------------|
| name  | string | Yes      | The user's name    |
| email | string | Yes      | Unique email address |

**Example request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

**Example response (201):**
```json
{
  "message": "User account created successfully.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### View User Profile

**`GET /api/users/{user_id}`**

Returns all wallets belonging to the user, each wallet's balance, and the overall balance across all wallets.

**Example response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallets": [
      { "id": 1, "name": "Business A", "balance": 1500.00 },
      { "id": 2, "name": "Personal",   "balance": 450.00  }
    ],
    "total_balance": 1950.00
  }
}
```

---

### Create a Wallet

**`POST /api/users/{user_id}/wallets`**

| Field | Type   | Required | Description               |
|-------|--------|----------|---------------------------|
| name  | string | Yes      | A friendly label for the wallet |

**Example request:**
```json
{
  "name": "Business A"
}
```

**Example response (201):**
```json
{
  "message": "Wallet created successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Business A",
    "balance": 0.00
  }
}
```

---

### View a Wallet

**`GET /api/wallets/{wallet_id}`**

Returns the wallet's balance and full transaction history (newest first).

**Example response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Business A",
    "user_id": 1,
    "balance": 350.00,
    "transactions": [
      {
        "id": 2,
        "wallet_id": 1,
        "type": "expense",
        "amount": 150.00,
        "description": "Office supplies",
        "created_at": "2024-01-02T10:00:00.000000Z"
      },
      {
        "id": 1,
        "wallet_id": 1,
        "type": "income",
        "amount": 500.00,
        "description": "Client payment",
        "created_at": "2024-01-01T09:00:00.000000Z"
      }
    ]
  }
}
```

---

### Add a Transaction

**`POST /api/wallets/{wallet_id}/transactions`**

| Field       | Type    | Required | Validation              |
|-------------|---------|----------|-------------------------|
| type        | string  | Yes      | Must be `income` or `expense` |
| amount      | decimal | Yes      | Must be greater than `0` |
| description | string  | No       | Max 500 characters      |

**Example request:**
```json
{
  "type": "income",
  "amount": 500.00,
  "description": "Client payment"
}
```

**Example response (201):**
```json
{
  "message": "Transaction recorded successfully.",
  "data": {
    "id": 1,
    "wallet_id": 1,
    "type": "income",
    "amount": 500.00,
    "description": "Client payment"
  },
  "wallet_balance": 500.00
}
```

---

## Design Decisions

**No authentication.** As specified, all endpoints are publicly accessible and users are identified by their ID in the URL.

**Dynamic balance calculation.** Balances are computed at query time rather than stored as a column. This keeps the data model simple and consistent — the balance is always a reflection of the actual transaction records.

**Cascading deletes.** If a user is deleted, their wallets are automatically removed. If a wallet is deleted, its transactions are removed. This maintains referential integrity without orphaned records.

**Positive amounts only.** The `amount` field is always stored as a positive decimal. The `type` column (`income` or `expense`) determines whether the amount adds to or subtracts from the balance. This makes queries and reporting straightforward.

---

## Suggested Commit History

Below is a recommended commit sequence that clearly demonstrates development progress:

```
feat: initial Laravel project setup
feat: created user, wallet, and transaction migrations
feat: added User, Wallet, and Transaction models with relationships
feat: implemented UserController (store and show)
feat: implemented WalletController (store and show)
feat: implemented TransactionController (store)
feat: defined API routes for users, wallets, and transactions
feat: added validation for all request fields
docs: added README with setup instructions and API documentation
```
# money-tracker
