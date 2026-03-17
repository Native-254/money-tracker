# Money Tracker API — Thunder Client Testing Guide

## Before Every Test Session

Run this in your terminal to start fresh:

```bash
php artisan migrate:fresh
php artisan serve
```

Open a notepad beside VS Code and keep track of your values as you go:

```
USER_A_ID  = 
TOKEN_A    = 

USER_B_ID  = 
TOKEN_B    = 

WALLET_1   = 
WALLET_2   = 
WALLET_3   = 
SHARED_WALLET = 
```

---

## IMPORTANT — Authorization Header Rule

Every protected request MUST have this header in the Headers tab:

```
Key:    Authorization
Value:  Bearer paste_your_token_here
```

The word Bearer followed by a space followed by the token is mandatory.
Without Bearer the API returns 401 Unauthenticated even with a valid token.
Without the token entirely the API returns 401 Unauthenticated.
Public routes (register, login) do NOT need this header.

---

## ═══════════════════════════════════════
## USER A — COMPLETE JOURNEY
## ═══════════════════════════════════════

---

## PHASE 1 — REGISTER USER A

### Test 1 — Register User A (success)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "name": "Howell Munene",
    "email": "howell@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Expected: 201 Created
```json
{
  "message": "Account created successfully.",
  "token": "1|abc...",
  "user": {
    "id": 1,
    "name": "Howell Munene",
    "email": "howell@gmail.com"
  }
}
```

Write down:
```
USER_A_ID = 1
TOKEN_A   = (copy the token value)
```

---

### Test 2 — Register with duplicate email (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "name": "Duplicate Howell",
    "email": "howell@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Expected: 422 Unprocessable Entity
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### Test 3 — Register with missing name (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "email": "noname@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Expected: 422 — error on name field

---

### Test 4 — Register with mismatched passwords (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "name": "Test User",
    "email": "test@gmail.com",
    "password": "password123",
    "password_confirmation": "wrongpassword"
}
```

Expected: 422 — error on password confirmation field

---

### Test 5 — Register with XSS attempt in name (sanitization)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "name": "<script>alert('xss')</script>Howell",
    "email": "xsstest@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Expected: 201 — check the name in response.
The script tags are stripped. Name stored as: alert('xss')Howell

---

## PHASE 2 — LOGIN USER A

### Test 6 — Login User A (success)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/login
Headers: Content-Type: application/json
Body:
{
    "email": "howell@gmail.com",
    "password": "password123"
}
```

Expected: 200 OK
```json
{
  "message": "Login successful.",
  "token": "2|xyz..."
}
```

Update your notepad — always use the most recent token:
```
TOKEN_A = (copy the new token)
```

---

### Test 7 — Login with wrong password (fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/login
Headers: Content-Type: application/json
Body:
{
    "email": "howell@gmail.com",
    "password": "wrongpassword"
}
```

Expected: 401 Unauthorized
```json
{
  "message": "Invalid credentials."
}
```

---

### Test 8 — Login with non-existent email (fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/login
Headers: Content-Type: application/json
Body:
{
    "email": "nobody@gmail.com",
    "password": "password123"
}
```

Expected: 401 Unauthorized

---

### Test 9 — Rate limit on login (brute force protection)

Send Test 7 six times in a row quickly.
On the 6th attempt:

Expected: 429 Too Many Requests
```json
{
  "message": "Too Many Attempts."
}
```

---

## PHASE 3 — WALLETS FOR USER A

For all wallet and transaction tests, the Authorization header is REQUIRED.

```
Key:    Authorization
Value:  Bearer TOKEN_A_VALUE
```

---

### Test 10 — Create Personal Wallet (success)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "name": "Personal Wallet"
}
```

Expected: 201 Created
```json
{
  "message": "Wallet created successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Personal Wallet",
    "balance": 0
  }
}
```

Write down:
```
WALLET_1 = 1
```

---

### Test 11 — Create Business Wallet (multiple wallets)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "name": "Business Wallet"
}
```

Expected: 201 Created

Write down:
```
WALLET_2 = 2
```

---

### Test 12 — Create Savings Wallet (multiple wallets)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "name": "House Savings Goal"
}
```

Expected: 201 Created

Write down:
```
WALLET_3 = 3
```

---

### Test 13 — Create wallet without token (fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Content-Type: application/json
(NO Authorization header)
Body:
{
    "name": "No Auth Wallet"
}
```

Expected: 401 Unauthenticated
```json
{
  "message": "Unauthenticated."
}
```

---

### Test 14 — Create wallet with missing name (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{}
```

Expected: 422 — error on name field

---

## PHASE 4 — TRANSACTIONS FOR USER A

### Test 15 — Add income to Personal Wallet

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 50000,
    "description": "Monthly salary"
}
```

Expected: 201 — wallet_balance: 50000

---

### Test 16 — Add second income (balance accumulates)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 5000,
    "description": "Freelance payment"
}
```

Expected: 201 — wallet_balance: 55000

---

### Test 17 — Add expense (balance decreases)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "expense",
    "amount": 12000,
    "description": "Rent"
}
```

Expected: 201 — wallet_balance: 43000

---

### Test 18 — Add expense to Business Wallet

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/2/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 120000,
    "description": "Client invoice paid"
}
```

Expected: 201 — wallet_balance: 120000

---

### Test 19 — Add income to Savings Wallet

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/3/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 10000,
    "description": "Monthly savings deposit"
}
```

Expected: 201 — wallet_balance: 10000

---

### Test 20 — Transaction with zero amount (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 0
}
```

Expected: 422 — error on amount field

---

### Test 21 — Transaction with negative amount (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "expense",
    "amount": -500
}
```

Expected: 422 — error on amount field

---

### Test 22 — Transaction with invalid type (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "transfer",
    "amount": 1000
}
```

Expected: 422 — error on type field

---

### Test 23 — XSS attempt in description (sanitization)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/1/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 1000,
    "description": "<script>alert('xss')</script>Bonus"
}
```

Expected: 201 — check description in response.
Stored as: alert('xss')Bonus (tags stripped)

---

## PHASE 5 — VIEW USER A PROFILE AND WALLETS

### Test 24 — View Personal Wallet

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/1
Headers: Authorization: Bearer TOKEN_A
```

Expected: 200 — balance 44000, all transactions listed newest first

---

### Test 25 — View Business Wallet

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/2
Headers: Authorization: Bearer TOKEN_A
```

Expected: 200 — balance 120000

---

### Test 26 — View Savings Wallet

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/3
Headers: Authorization: Bearer TOKEN_A
```

Expected: 200 — balance 10000

---

### Test 27 — View User A full profile (all wallets + total balance)

```
Method:  GET
URL:     http://127.0.0.1:8000/api/users/1
Headers: Authorization: Bearer TOKEN_A
```

Expected: 200
```json
{
  "data": {
    "id": 1,
    "name": "Howell Munene",
    "email": "howell@gmail.com",
    "wallets": [
      { "id": 1, "name": "Personal Wallet",   "balance": 44000 },
      { "id": 2, "name": "Business Wallet",   "balance": 120000 },
      { "id": 3, "name": "House Savings Goal","balance": 10000 }
    ],
    "total_balance": 174000
  }
}
```

---

### Test 28 — View wallet without token (fail)

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/1
(NO Authorization header)
```

Expected: 401 Unauthenticated

---

## PHASE 6 — LOGOUT USER A

### Test 29 — Logout User A

```
Method:  POST
URL:     http://127.0.0.1:8000/api/logout
Headers: Authorization: Bearer TOKEN_A
```

Expected: 200
```json
{
  "message": "Logged out successfully."
}
```

---

### Test 30 — Use token after logout (token invalidated)

```
Method:  GET
URL:     http://127.0.0.1:8000/api/users/1
Headers: Authorization: Bearer TOKEN_A  (the old token)
```

Expected: 401 Unauthenticated — token no longer exists

---

## ═══════════════════════════════════════
## USER B — COMPLETE JOURNEY
## ═══════════════════════════════════════

### Test 31 — Register User B

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
    "name": "Jane Doe",
    "email": "jane@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Expected: 201 Created

Write down:
```
USER_B_ID = (copy id from response)
TOKEN_B   = (copy token from response)
```

---

### Test 32 — Login User B

```
Method:  POST
URL:     http://127.0.0.1:8000/api/login
Headers: Content-Type: application/json
Body:
{
    "email": "jane@gmail.com",
    "password": "password123"
}
```

Expected: 200 — update TOKEN_B with new token

---

### Test 33 — Create wallet for User B

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/USER_B_ID/wallets
Headers: Authorization: Bearer TOKEN_B
         Content-Type: application/json
Body:
{
    "name": "Jane Personal Wallet"
}
```

Expected: 201 Created

---

### Test 34 — User B tries to access User A's wallet (authorization fail)

Login as User A first to get a fresh TOKEN_A, then:

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/1
Headers: Authorization: Bearer TOKEN_B  (Jane's token)
```

Expected: 403 Unauthorized
```json
{
  "message": "Unauthorized."
}
```

---

### Test 35 — User B tries to view User A's profile (authorization fail)

```
Method:  GET
URL:     http://127.0.0.1:8000/api/users/1
Headers: Authorization: Bearer TOKEN_B
```

Expected: 403 Unauthorized

---

### Test 36 — User B tries to create wallet for User A (authorization fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_B
         Content-Type: application/json
Body:
{
    "name": "Unauthorized Wallet"
}
```

Expected: 403 Unauthorized

---

## ═══════════════════════════════════════
## SHARED WALLETS
## ═══════════════════════════════════════

Log back in as User A to get a fresh token before this phase.

```
Method:  POST
URL:     http://127.0.0.1:8000/api/login
Body:    { "email": "howell@gmail.com", "password": "password123" }
```

Update TOKEN_A with the new token.

---

### Test 37 — User A creates a shared wallet

```
Method:  POST
URL:     http://127.0.0.1:8000/api/users/1/wallets
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "name": "Joint Family Savings"
}
```

Expected: 201 Created

Write down:
```
SHARED_WALLET = (copy id from response)
```

---

### Test 38 — User A invites User B to the shared wallet

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/invite
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "email": "jane@gmail.com"
}
```

Expected: 200
```json
{
  "message": "User added to wallet successfully."
}
```

---

### Test 39 — Invite same user twice (conflict fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/invite
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "email": "jane@gmail.com"
}
```

Expected: 409 Conflict
```json
{
  "message": "User is already a member of this wallet."
}
```

---

### Test 40 — Invite non-existent user (validation fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/invite
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "email": "nobody@gmail.com"
}
```

Expected: 422 — email does not exist in users table

---

### Test 41 — User B tries to invite someone (only owner can invite — fail)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/invite
Headers: Authorization: Bearer TOKEN_B
         Content-Type: application/json
Body:
{
    "email": "howell@gmail.com"
}
```

Expected: 403
```json
{
  "message": "Only the wallet owner can invite members."
}
```

---

### Test 42 — User B adds income to shared wallet (member access)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/transactions
Headers: Authorization: Bearer TOKEN_B
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 15000,
    "description": "Jane's contribution"
}
```

Expected: 201 — wallet_balance: 15000

---

### Test 43 — User A adds expense to shared wallet (owner access)

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/transactions
Headers: Authorization: Bearer TOKEN_A
         Content-Type: application/json
Body:
{
    "type": "expense",
    "amount": 3000,
    "description": "Shared grocery run"
}
```

Expected: 201 — wallet_balance: 12000

---

### Test 44 — User B views shared wallet (member can view)

```
Method:  GET
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET
Headers: Authorization: Bearer TOKEN_B
```

Expected: 200 — balance 12000, both transactions visible

---

### Test 45 — Unrelated user tries to add transaction to shared wallet (fail)

Register a third user first:

```
Method:  POST
URL:     http://127.0.0.1:8000/api/register
Body:
{
    "name": "Stranger",
    "email": "stranger@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Copy TOKEN_C, then:

```
Method:  POST
URL:     http://127.0.0.1:8000/api/wallets/SHARED_WALLET/transactions
Headers: Authorization: Bearer TOKEN_C
         Content-Type: application/json
Body:
{
    "type": "income",
    "amount": 999
}
```

Expected: 403 Unauthorized — not a wallet member

---

## ═══════════════════════════════════════
## QUICK RESET BETWEEN DEMO RUNS
## ═══════════════════════════════════════

```bash
php artisan migrate:fresh
php artisan serve
```

This wipes all data and restarts cleanly.
IDs reset to 1. All tokens are invalidated.
Start again from Test 1.
