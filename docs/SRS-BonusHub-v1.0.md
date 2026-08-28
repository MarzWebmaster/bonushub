# BonusHub — Software Requirements Specification (SRS)

**Version:** 1.1
**Date:** 28 Ogos 2026
**Author:** Marz Technology & Trading
**Status:** Live (bonushub.my)
**Reference:** PRD-BonusHub-v1.1.md

---

## 1. Introduction

### 1.1 Purpose
Dokumen ini mentakrifkan keperluan teknikal untuk platform BonusHub — SaaS loyalty, viral loop, dan giveaway untuk bisnes Malaysia.

### 1.2 Scope
- **Royalty Engine:** Points earning, redemption, tier system, rewards catalog
- **Viral Engine:** Social task creation, execution, verification, anti-fraud
- **Giveaway Engine:** Referral campaigns, leaderboard, winner selection
- **Platform:** Merchant dashboard, customer app (PWA), admin panel

### 1.3 Definitions
| Term | Definition |
|------|-----------|
| Merchant | Business yang guna BonusHub untuk loyalty program |
| Customer | End-user yang collect & redeem points |
| Staff | Merchant employees yang proses transaksi |
| Superadmin | BonusHub platform admin |
| Points | Virtual currency diberikan oleh merchant |
| Task | Viral task yang customer perlu complete |
| Campaign | Giveaway/referral contest oleh merchant |
| Tier | Customer loyalty level (Regular, Silver, Gold, Platinum) |

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────┐
│                    CLIENTS                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │ Merchant │  │ Customer │  │  Admin   │      │
│  │Dashboard │  │   PWA    │  │  Panel   │      │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘      │
│       │              │              │            │
│       └──────────────┼──────────────┘            │
│                      │                           │
│               ┌──────┴──────┐                    │
│               │   Laravel   │                    │
│               │   Backend   │                    │
│               └──────┬──────┘                    │
│                      │                           │
│    ┌─────────────────┼─────────────────┐        │
│    │                 │                 │        │
│ ┌──┴──┐         ┌───┴───┐        ┌───┴───┐   │
│ │MySQL│         │ Redis │        │Queue  │   │
│ │ 8.0 │         │  7.x  │        │Worker │   │
│ └─────┘         └───────┘        └───────┘   │
└─────────────────────────────────────────────────┘
```

### 2.2 Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Backend Framework | Laravel | 11.x |
| PHP | PHP | 8.2+ |
| Database | MySQL | 8.0 |
| Cache/Queue | Redis | 7.x |
| Web Server | Apache (via Sail) | Latest |
| Dev Environment | Docker Sail / PHP-FPM | Latest |
| Frontend (Admin) | Blade + Alpine.js + TailwindCSS | Latest |
| Frontend (Customer) | Blade PWA | Latest |
| Queue Worker | Laravel Horizon | Latest |
| Mail | Postfix + Dovecot (production) / Mailpit (dev) | - |
| Search | Laravel Scout + Meilisearch (optional) | - |

### 2.3 Deployment Architecture

```
Production (bonushub.my):
- OVH VPS (Ubuntu 22.04)
- Nginx reverse proxy (SSL via Cloudflare)
- PHP-FPM 8.3
- MySQL 8.0
- Redis 7
- Supervisor (queue workers)
- Cloudflare DNS + SSL
- Postfix + Dovecot (mail server at mail.bonushub.my)
- OpenDKIM (email authentication)

Staging/Dev:
- Docker Sail / PHP-FPM (local)
- Mailpit for email testing
- SQLite for local development
```

---

## 3. Database Schema — EXISTING (Not Changed)

### 3.1 packages
```sql
CREATE TABLE packages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0,
    branch_limit INT DEFAULT 1,
    staff_limit INT DEFAULT 5,
    giveaway_limit INT DEFAULT 0,
    task_limit INT DEFAULT 0,
    features JSON,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.2 merchants
```sql
CREATE TABLE merchants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255),
    logo VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    status VARCHAR(255) DEFAULT 'active',
    package_id BIGINT UNSIGNED NULL,
    subscription_expiry TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.3 branches
```sql
CREATE TABLE branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    name VARCHAR(255),
    address TEXT,
    phone VARCHAR(20),
    status VARCHAR(255) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.4 customers
```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    phone VARCHAR(20) NULL UNIQUE,
    email VARCHAR(255) NULL UNIQUE,
    password VARCHAR(255) NULL,
    tier_global VARCHAR(255) DEFAULT 'regular',
    birthdate DATE NULL,
    registered_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.5 customer_merchant (Pivot)
```sql
CREATE TABLE customer_merchant (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED,
    merchant_id BIGINT UNSIGNED,
    points DECIMAL(15,2) DEFAULT 0,
    tier_per_merchant VARCHAR(255) DEFAULT 'regular',
    tied_at TIMESTAMP NULL,
    campaign_link_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (customer_id, merchant_id)
);
```

### 3.6 loyalty_rates
```sql
CREATE TABLE loyalty_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    rate_per_rm DECIMAL(10,2) DEFAULT 1.00,
    earn_rate DECIMAL(10,2) DEFAULT 1.00,
    redeem_rate DECIMAL(10,2) DEFAULT 1.00,
    min_redeem INT NULL,
    max_redeem INT NULL,
    festive_multiplier JSON,
    product_specific_rules JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.7 merchant_rewards
```sql
CREATE TABLE merchant_rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    name VARCHAR(255),
    description TEXT NULL,
    points_required DECIMAL(15,2) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    stock_left INT DEFAULT 0,
    claim_type ENUM('self_collect','delivery','download','access_code') DEFAULT 'self_collect',
    delivery_cost ENUM('merchant','customer','none') DEFAULT 'none',
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    download_url VARCHAR(255) NULL,
    access_code_prefix VARCHAR(255) NULL,
    status VARCHAR(255) DEFAULT 'active',
    image VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.8 points_transactions
```sql
CREATE TABLE points_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED,
    merchant_id BIGINT UNSIGNED,
    branch_id BIGINT UNSIGNED NULL,
    staff_id BIGINT UNSIGNED NULL,
    type ENUM('earn','redeem','void','expired'),
    points DECIMAL(15,2) DEFAULT 0,
    amount_spent DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    approved_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.9 redemptions
```sql
CREATE TABLE redemptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED,
    merchant_id BIGINT UNSIGNED,
    reward_id BIGINT UNSIGNED,
    points_used DECIMAL(15,2) DEFAULT 0,
    cash_topup DECIMAL(15,2) DEFAULT 0,
    claim_method VARCHAR(255) NULL,
    status ENUM('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
    claim_code VARCHAR(255) NULL UNIQUE,
    staff_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.10 merchant_tiers
```sql
CREATE TABLE merchant_tiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    tier_name VARCHAR(255),
    min_points INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (merchant_id, tier_name)
);
```

### 3.11 promos
```sql
CREATE TABLE promos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    name VARCHAR(255),
    type ENUM('registration_bonus','multiplier','fixed_bonus'),
    value DECIMAL(10,2),
    status ENUM('active','inactive') DEFAULT 'active',
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.12 campaign_links
```sql
CREATE TABLE campaign_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    medium VARCHAR(255) NULL,
    visits INT UNSIGNED DEFAULT 0,
    registrations INT UNSIGNED DEFAULT 0,
    status VARCHAR(255) DEFAULT 'active',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 4. Database Schema — NEW (Viral Engine)

### 4.1 viral_tasks
```sql
CREATE TABLE viral_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    task_type ENUM('social_follow','social_like','social_comment','social_share','google_review','visit_store','attend_event','custom') NOT NULL,
    platform ENUM('instagram','tiktok','facebook','twitter','youtube','google','other') NULL,
    points_reward DECIMAL(15,2) DEFAULT 0,
    verification_method ENUM('auto_track','honor_system','screenshot_proof') DEFAULT 'honor_system',
    deadline TIMESTAMP NULL,
    max_participants INT UNSIGNED NULL,
    max_per_customer INT UNSIGNED DEFAULT 1,
    status ENUM('active','paused','completed','expired') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_merchant_status (merchant_id, status)
);
```

### 4.2 viral_task_submissions
```sql
CREATE TABLE viral_task_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    merchant_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','verified','rejected','expired') DEFAULT 'pending',
    proof_type ENUM('auto','checkbox','screenshot') NOT NULL,
    proof_url VARCHAR(500) NULL,
    proof_notes TEXT NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    points_awarded DECIMAL(15,2) DEFAULT 0,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (task_id, customer_id),
    INDEX idx_task_status (task_id, status),
    INDEX idx_customer_status (customer_id, status),
    INDEX idx_merchant_status (merchant_id, status)
);
```

### 4.3 viral_task_links
```sql
CREATE TABLE viral_task_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    unique_slug VARCHAR(50) NOT NULL UNIQUE,
    utm_source VARCHAR(100) NULL,
    utm_medium VARCHAR(100) NULL,
    utm_campaign VARCHAR(100) NULL,
    clicks INT UNSIGNED DEFAULT 0,
    conversions INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    INDEX idx_task (task_id),
    INDEX idx_customer (customer_id),
    INDEX idx_slug (unique_slug)
);
```

### 4.4 viral_task_analytics
```sql
CREATE TABLE viral_task_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    views INT UNSIGNED DEFAULT 0,
    clicks INT UNSIGNED DEFAULT 0,
    submissions INT UNSIGNED DEFAULT 0,
    verified INT UNSIGNED DEFAULT 0,
    rejected INT UNSIGNED DEFAULT 0,
    points_spent DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (task_id, date)
);
```

---

## 5. Database Schema — NEW (Giveaway Engine)

### 5.1 giveaway_campaigns
```sql
CREATE TABLE giveaway_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    campaign_type ENUM('referral_contest','milestone','flash','seasonal') DEFAULT 'referral_contest',
    prize_type ENUM('reward_catalog','custom') DEFAULT 'custom',
    prize_reward_id BIGINT UNSIGNED NULL,
    prize_description TEXT NULL,
    prize_image VARCHAR(255) NULL,
    prize_value_points DECIMAL(15,2) DEFAULT 0,
    start_at TIMESTAMP NOT NULL,
    end_at TIMESTAMP NOT NULL,
    referral_goal INT UNSIGNED NULL,
    winner_count INT UNSIGNED DEFAULT 1,
    participation_points INT UNSIGNED DEFAULT 0,
    max_participants INT UNSIGNED NULL,
    status ENUM('draft','active','completed','cancelled') DEFAULT 'draft',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_merchant_status (merchant_id, status)
);
```

### 5.2 giveaway_entries
```sql
CREATE TABLE giveaway_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    merchant_id BIGINT UNSIGNED NOT NULL,
    referral_count INT UNSIGNED DEFAULT 0,
    rank INT UNSIGNED NULL,
    status ENUM('participating','winner','completed') DEFAULT 'participating',
    won_at TIMESTAMP NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (campaign_id, customer_id),
    INDEX idx_campaign_status (campaign_id, status),
    INDEX idx_customer (customer_id)
);
```

### 5.3 giveaway_referrals
```sql
CREATE TABLE giveaway_referrals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    referrer_id BIGINT UNSIGNED NOT NULL,
    referred_id BIGINT UNSIGNED NOT NULL,
    merchant_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','valid','invalid') DEFAULT 'pending',
    validated_at TIMESTAMP NULL,
    invalid_reason VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (campaign_id, referred_id),
    INDEX idx_campaign_status (campaign_id, status),
    INDEX idx_referrer (referrer_id)
);
```

### 5.4 giveaway_shares
```sql
CREATE TABLE giveaway_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    platform ENUM('whatsapp','facebook','instagram','twitter','telegram','other') NOT NULL,
    share_url VARCHAR(500) NULL,
    clicks INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    INDEX idx_campaign (campaign_id),
    INDEX idx_customer (customer_id)
);
```

### 5.5 giveaway_winners
```sql
CREATE TABLE giveaway_winners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    entry_id BIGINT UNSIGNED NOT NULL,
    rank_position INT UNSIGNED NOT NULL,
    prize_description TEXT NULL,
    prize_value_points DECIMAL(15,2) DEFAULT 0,
    status ENUM('selected','notified','claimed','expired') DEFAULT 'selected',
    notified_at TIMESTAMP NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (campaign_id, customer_id)
);
```

---

## 6. Database Schema — NEW (Fraud Detection)

### 6.1 fraud_flags
```sql
CREATE TABLE fraud_flags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('viral_submission','giveaway_referral') NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    merchant_id BIGINT UNSIGNED NOT NULL,
    flag_type ENUM('duplicate_ip','fast_completion','high_volume','suspicious_pattern','manual_flag') NOT NULL,
    severity ENUM('low','medium','high') DEFAULT 'medium',
    description TEXT NULL,
    status ENUM('flagged','reviewed','cleared','confirmed_fraud') DEFAULT 'flagged',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_merchant_status (merchant_id, status)
);
```

### 6.2 fraud_rules
```sql
CREATE TABLE fraud_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_type ENUM('ip_limit','velocity_limit','daily_limit','global') NOT NULL,
    config JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 7. API Endpoints

### 7.1 Auth (Web Routes — Implemented)
```
GET    /login                          — Login page
POST   /login                          — Login submit
GET    /register                       — Customer register page
POST   /register                       — Customer register submit (with OTP validation)
GET    /merchant/register              — Merchant register page
POST   /merchant/register              — Merchant register submit (with OTP validation)
GET    /merchant/verification          — Merchant IC/SSM upload page
POST   /merchant/verification          — Upload documents
POST   /merchant/verification/skip     — Skip document upload
POST   /logout                         — Logout

OTP Routes:
POST   /otp/send                       — Send OTP to email (AJAX)
POST   /otp/verify                     — Verify OTP code (AJAX)

Superadmin:
GET    /superadmin/merchants-pending   — List pending merchants
POST   /superadmin/merchants/{id}/approve — Approve merchant
POST   /superadmin/merchants/{id}/reject  — Reject merchant
```

### 7.1b Auth (API — Legacy/Planned)
```
POST   /api/login                    — Customer login (phone + OTP)
POST   /api/register                 — Customer register
POST   /api/merchant/login           — Merchant login
POST   /api/staff/login              — Staff login
POST   /api/logout                   — Logout
```

### 7.2 Customer — Royalty
```
GET    /api/customer/profile         — Get profile
PUT    /api/customer/profile         — Update profile
GET    /api/customer/points/balance  — Get points balance (per merchant)
GET    /api/customer/points/history  — Get points transaction history
GET    /api/customer/merchants       — List joined merchants
POST   /api/customer/merchants/{id}/join  — Join merchant
GET    /api/customer/leaderboard/{merchant_id}  — Leaderboard
GET    /api/customer/rewards/{merchant_id}      — Available rewards
POST   /api/customer/redeem          — Redeem reward
GET    /api/customer/redemptions     — My redemption history
GET    /api/customer/tiers/{merchant_id}  — Tier info
```

### 7.3 Customer — Viral Engine
```
GET    /api/customer/tasks           — Browse available tasks
GET    /api/customer/tasks/{id}      — Task detail
POST   /api/customer/tasks/{id}/submit    — Submit task completion
GET    /api/customer/tasks/submissions    — My submissions history
GET    /api/customer/tasks/{id}/link      — Get my unique task link
POST   /api/customer/tasks/{id}/share     — Record share action
```

### 7.4 Customer — Giveaway Engine
```
GET    /api/customer/campaigns              — Browse active campaigns
GET    /api/customer/campaigns/{id}         — Campaign detail
POST   /api/customer/campaigns/{id}/join    — Join campaign
GET    /api/customer/campaigns/{id}/leaderboard  — Campaign leaderboard
GET    /api/customer/campaigns/{id}/referral-link — Get referral link
POST   /api/customer/campaigns/{id}/share   — Record share action
GET    /api/customer/campaigns/my           — My joined campaigns
```

### 7.5 Merchant — Dashboard
```
GET    /api/merchant/dashboard/stats        — Dashboard statistics
GET    /api/merchant/dashboard/recent       — Recent activity
```

### 7.6 Merchant — Customer Management
```
GET    /api/merchant/customers              — Customer list (filterable)
GET    /api/merchant/customers/{id}         — Customer detail
POST   /api/merchant/customers/earn-points  — Award points manually
POST   /api/merchant/customers/scan-qr      — QR code points earning
```

### 7.7 Merchant — Rewards
```
GET    /api/merchant/rewards                — Reward catalog
POST   /api/merchant/rewards                — Create reward
PUT    /api/merchant/rewards/{id}           — Update reward
DELETE /api/merchant/rewards/{id}           — Delete reward
```

### 7.8 Merchant — Tiers
```
GET    /api/merchant/tiers                  — Tier configuration
PUT    /api/merchant/tiers                  — Update tiers
```

### 7.9 Merchant — Loyalty Rates
```
GET    /api/merchant/loyalty-rates          — Get rates
PUT    /api/merchant/loyalty-rates          — Update rates
```

### 7.10 Merchant — Promos
```
GET    /api/merchant/promos                 — List promos
POST   /api/merchant/promos                 — Create promo
PUT    /api/merchant/promos/{id}            — Update promo
DELETE /api/merchant/promos/{id}            — Delete promo
```

### 7.11 Merchant — Viral Engine
```
GET    /api/merchant/viral/tasks            — List viral tasks
POST   /api/merchant/viral/tasks            — Create viral task
PUT    /api/merchant/viral/tasks/{id}       — Update viral task
DELETE /api/merchant/viral/tasks/{id}       — Delete viral task
GET    /api/merchant/viral/submissions      — Pending submissions
POST   /api/merchant/viral/submissions/{id}/approve  — Approve
POST   /api/merchant/viral/submissions/{id}/reject   — Reject
GET    /api/merchant/viral/analytics/{task_id}  — Task analytics
GET    /api/merchant/viral/fraud-flags      — Fraud flags list
POST   /api/merchant/viral/fraud-flags/{id}/review  — Review flag
```

### 7.12 Merchant — Giveaway Engine
```
GET    /api/merchant/giveaways              — List campaigns
POST   /api/merchant/giveaways              — Create campaign
PUT    /api/merchant/giveaways/{id}         — Update campaign
DELETE /api/merchant/giveaways/{id}         — Delete campaign
GET    /api/merchant/giveaways/{id}/entries — Campaign entries
POST   /api/merchant/giveaways/{id}/select-winners  — Select winners
GET    /api/merchant/giveaways/{id}/analytics  — Campaign analytics
```

### 7.13 Merchant — Campaign Links
```
GET    /api/merchant/campaigns              — List campaign links
POST   /api/merchant/campaigns              — Create campaign link
PUT    /api/merchant/campaigns/{id}         — Update
DELETE /api/merchant/campaigns/{id}         — Delete
GET    /api/merchant/campaigns/{id}/analytics — Analytics
```

### 7.14 Merchant — Reports
```
GET    /api/merchant/reports/liability      — Points liability report
GET    /api/merchant/reports/revenue        — Revenue report
GET    /api/merchant/reports/customer-segmentation  — RFM analysis
GET    /api/merchant/reports/viral-roi      — Viral campaign ROI
```

### 7.15 Staff
```
GET    /api/staff/dashboard                 — Staff dashboard
POST   /api/staff/points/earn               — Process points earn
POST   /api/staff/points/redeem             — Process points redeem
GET    /api/staff/pending-approvals         — Pending approvals
POST   /api/staff/approve/{id}              — Approve transaction
POST   /api/staff/reject/{id}               — Reject transaction
```

### 7.16 Admin (Superadmin)
```
GET    /api/admin/dashboard                 — Platform dashboard
GET    /api/admin/merchants                 — Merchant list
GET    /api/admin/packages                  — Package management
GET    /api/admin/analytics                 — Platform analytics
```

### 7.17 Public
```
GET    /r/{slug}                            — Campaign redirect
GET    /t/{slug}                            — Viral task redirect
GET    /g/{slug}                            — Giveaway campaign redirect
GET    /api/public/merchant/{id}/rewards    — Public rewards (customer scan QR)
POST   /api/public/claim/{claim_code}       — Claim redemption code
```

---

## 8. Security Requirements

### 8.1 Authentication
- **Customer:** Email + OTP verification (6-digit, email-based). OTP wajib verified sebelum submit register.
- **Merchant:** Email + OTP verification (same flow). Lepas register, merchant perlu upload IC/SSM atau skip.
- **Superadmin:** Email + password (admin@bonushub.com). Login page: `/login`
- **Session:** Laravel session-based authentication (not Sanctum SPA for web routes)
- **OTP Storage:** `verification_otps` table (email, code, type, expires_at, is_used)
- **OTP Expiry:** 10 minutes per code

### 8.2 Authorization (RBAC)
- **Superadmin:** Full platform access, merchant approval/rejection
- **Merchant Admin:** Full merchant-level access (DILULUSKAN sahaja — CheckMerchantApproved middleware)
- **Staff:** Limited (process transactions, view customers, approve tasks)
- **Customer:** Own profile, points, rewards, tasks, campaigns
- **Merchant Approval Flow:** `pending_verification` → `pending_approval` → `active`/`rejected`
- **Middleware:**
  - `CheckMerchantApproved`: Redirect merchant ke `/merchant/verification` jika status bukan `active`
  - `CheckApproved`: Alias untuk merchant access control

### 8.3 Data Protection
- **PDPA Compliance:** Customer consent for data collection
- **Password hashing:** bcrypt (Laravel default)
- **Sensitive data encryption:** Phone, email at rest
- **API rate limiting:** 60 requests/minute per user
- **CORS:** Restricted to own domains
- **CSRF:** Laravel CSRF tokens on all forms
- **XSS:** Blade auto-escaping, sanitize user input
- **SQL Injection:** Eloquent ORM (parameterized queries)

### 8.4 Anti-Fraud
- **IP tracking:** Log IP for all submissions
- **Rate limiting:** Max tasks/campaigns per day
- **Duplicate detection:** Unique constraints on submissions
- **Manual review:** Staff approval for suspicious activity
- **Audit trail:** All transactions logged in activity_logs

### 8.5 Infrastructure
- **SSL/TLS:** HTTPS everywhere (Let's Encrypt)
- **Database backups:** Daily automated
- **Queue security:** Redis AUTH in production
- **Environment variables:** Never committed to git
- **File uploads:** Validate type, size; store outside web root

---

## 9. Performance Requirements

### 9.1 Response Time
| Operation | Target |
|-----------|--------|
| API response (read) | < 200ms (p95) |
| API response (write) | < 500ms (p95) |
| Dashboard page load | < 2s |
| Customer PWA load | < 3s (3G) |
| QR code scan → points credit | < 3s |

### 9.2 Throughput
| Metric | Target |
|--------|--------|
| Concurrent users | 1,000 |
| API requests/second | 100 |
| Points transactions/second | 50 |
| Queue jobs/minute | 500 |

### 9.3 Scalability
- **Database:** Read replicas for reporting queries
- **Cache:** Redis for session, dashboard stats, leaderboard
- **Queue:** Laravel Horizon with multiple workers
- **CDN:** Static assets via Cloudflare
- **Storage:** Local/S3 for file uploads (screenshots)

### 9.4 Availability
- **Uptime target:** 99.9% (8.76 hours downtime/year)
- **Recovery time objective (RTO):** 4 hours
- **Recovery point objective (RPO):** 1 hour

---

## 10. Integration Points

### 10.1 Payment Gateway
- **ToyyibPay** — Subscription billing for merchants
- **FPX** — Direct bank transfer
- **Touch 'n Go eWallet** — Optional

### 10.2 SMS Gateway
- **MSG91** or **Nadi SMS** — OTP for customer registration
- **Template:** "BonusHub: Kod OTP anda ialah {code}. Sah dalam 5 minit."

### 10.3 Email
- **SMTP** — Transactional emails (registration, redemption, campaign)
- **Mailpit** — Development/testing

### 10.4 Social Media
- **WhatsApp API** — Share campaign links
- **Facebook/Meta API** — Share tracking (optional)
- **Instagram API** — Follow verification (future)
- **TikTok API** — Follow verification (future)

### 10.5 Analytics
- **Google Analytics 4** — Campaign link tracking
- **Meta Pixel** — Retargeting (optional)
- **Internal analytics** — Points, tasks, campaigns

### 10.6 QR Code
- **QR Code generation:** `chillerlan/php-qrcode` or `bacon/bacon-qr-code`
- **Format:** QR code URL ke customer profile / task / campaign
- **Scan:** Staff scan via merchant app

---

## 11. Queue Jobs

### 11.1 Critical Jobs
```php
// Points
App\Jobs\ProcessPointsEarning
App\Jobs\ProcessPointsRedemption
App\Jobs\ProcessPointsExpiry
App\Jobs\ProcessTierUpgrade

// Viral
App\Jobs\ProcessViralTaskSubmission
App\Jobs\VerifyViralTaskSubmission
App\Jobs\AwardViralTaskPoints
App\Jobs\DetectViralFraud

// Giveaway
App\Jobs\ProcessGiveawayEntry
App\Jobs\ValidateGiveawayReferral
App\Jobs\SelectGiveawayWinners
App\Jobs\NotifyGiveawayWinners

// Notifications
App\Jobs\SendPushNotification
App\Jobs\SendEmailNotification
App\Jobs\SendSmsOtp
```

### 11.2 Scheduled Tasks
```php
// Daily at 00:00
$schedule->job(new ProcessPointsExpiry)->daily();
$schedule->job(new GenerateViralTaskAnalytics)->daily();
$schedule->job(new CompleteExpiredGiveaways)->daily();

// Every hour
$schedule->job(new CheckGiveawayDeadlines)->hourly();
$schedule->job(new CleanupExpiredTasks)->hourly();

// Every 5 minutes
$schedule->job(new MonitorFraudPatterns)->everyFiveMinutes();
```

---

## 12. Error Handling

### 12.1 HTTP Status Codes
| Code | Usage |
|------|-------|
| 200 | Success |
| 201 | Created |
| 400 | Bad request (validation error) |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not found |
| 409 | Conflict (already exists) |
| 422 | Validation error |
| 429 | Rate limited |
| 500 | Server error |

### 12.2 Error Response Format
```json
{
    "success": false,
    "message": "Human-readable error message",
    "errors": {
        "field_name": ["Error detail 1", "Error detail 2"]
    }
}
```

### 12.3 Success Response Format
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

---

## 13. Testing Requirements

### 13.1 Unit Tests
- Models: Relationships, scopes, helpers
- Services: Business logic, calculations
- Target: 80% coverage

### 13.2 Feature Tests
- API endpoints: Request/Response validation
- Auth flows: Login, register, OTP
- Points flow: Earn, redeem, expire
- Viral flow: Create, submit, verify
- Giveaway flow: Join, refer, win

### 13.3 Integration Tests
- Full user journey: Register → Join → Earn → Redeem
- Viral journey: Create task → Submit → Verify → Points
- Giveaway journey: Create → Join → Refer → Win

### 13.4 Browser Tests (Selenium)
- Dashboard UI flows
- Customer PWA flows
- QR code scan flow

---

## 14. Deployment Checklist

### 14.1 Pre-Deployment
- [ ] All tests passing
- [ ] Database migrations tested
- [ ] Environment variables configured
- [ ] SSL certificate installed
- [ ] Queue workers configured
- [ ] Cron jobs configured
- [ ] Backup strategy in place

### 14.2 Deployment Steps
1. Pull latest code
2. Run `composer install --no-dev`
3. Run `php artisan migrate --force`
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Run `php artisan view:cache`
7. Restart queue workers
8. Restart web server
9. Verify health check endpoint

### 14.3 Post-Deployment
- [ ] Monitor error logs
- [ ] Verify API endpoints
- [ ] Test critical user flows
- [ ] Check queue processing
- [ ] Verify email delivery

---

## Appendix A: Configuration

### Points Configuration (per merchant)
```php
'points' => [
    'earn_rate' => 1.00,        // Points per RM1 spent
    'redeem_rate' => 1.00,      // RM1 discount per X points
    'min_redeem' => 100,        // Minimum points to redeem
    'max_redeem' => 1000,       // Maximum points per redemption
    'expiry_months' => 12,      // Points expiry in months
    'transfer_enabled' => false, // Allow points transfer
    'birthday_bonus' => 50,     // Birthday bonus points
    'referral_bonus' => 100,    // Referral bonus points
]
```

### Viral Task Configuration (per merchant)
```php
'viral' => [
    'max_tasks_per_day' => 5,
    'max_tasks_per_customer' => 3,
    'auto_approve_threshold' => 100,  // Auto-approve if points < this
    'screenshot_required_above' => 100, // Screenshot required if points > this
    'ip_limit_per_task' => 3,         // Max completions per IP per task
    'velocity_limit_minutes' => 5,    // Min minutes between submissions
]
```

### Giveaway Configuration (per merchant)
```php
'giveaway' => [
    'max_active_campaigns' => 3,
    'min_duration_hours' => 24,
    'max_duration_days' => 90,
    'referral_validation_days' => 7,  // Referral must be active for X days
    'winner_notification_days' => 7,  // Winner must claim within X days
]
```

---



---

## 15. Points Wallet System (NEW)

### 15.1 Database Tables

```sql
-- Points Wallet
CREATE TABLE points_wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    balance BIGINT DEFAULT 0,
    total_earned BIGINT DEFAULT 0,
    total_redeemed BIGINT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (customer_id),
    INDEX idx_customer (customer_id)
);

-- Points Transactions
CREATE TABLE points_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT UNSIGNED NOT NULL,
    type ENUM('earn','redeem','expire','transfer','adjustment') NOT NULL,
    amount BIGINT NOT NULL,
    balance_after BIGINT NOT NULL,
    reference_type VARCHAR(255) NULL,
    reference_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    created_at TIMESTAMP,
    INDEX idx_wallet (wallet_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
);

-- Withdrawal Requests (for REDEEM_CASH)
CREATE TABLE withdrawal_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    wallet_id BIGINT UNSIGNED NOT NULL,
    points_amount BIGINT NOT NULL,
    rm_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','approved','rejected','paid') DEFAULT 'pending',
    bank_name VARCHAR(100) NOT NULL,
    bank_account VARCHAR(50) NOT NULL,
    bank_holder VARCHAR(255) NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
);

-- Merchant Products
CREATE TABLE merchant_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(500) NULL,
    points_price BIGINT NOT NULL,
    stock_quantity INT DEFAULT 0,
    status ENUM('active','inactive','out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_merchant (merchant_id),
    INDEX idx_status (status)
);

-- Feature Toggles
CREATE TABLE feature_toggles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value BOOLEAN DEFAULT FALSE,
    label VARCHAR(255) NULL,
    description TEXT NULL,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP,
    INDEX idx_key (`key`)
);

-- Platform Settings
CREATE TABLE platform_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NULL,
    type ENUM('string','integer','boolean','json') DEFAULT 'string',
    label VARCHAR(255) NULL,
    description TEXT NULL,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP,
    INDEX idx_key (`key`)
);
```

### 15.2 API Endpoints

```
-- Points Wallet
GET    /api/customer/wallet              — Get wallet balance & stats
GET    /api/customer/wallet/transactions — Get transaction history

-- Points Redemption (Items)
GET    /api/customer/products            — Browse merchant products
GET    /api/customer/products/{id}       — Product detail
POST   /api/customer/products/{id}/redeem — Redeem product with points
GET    /api/customer/redemptions         — My redemption history

-- Points Redemption (Cash)
POST   /api/customer/withdraw            — Request cash withdrawal
GET    /api/customer/withdrawals         — My withdrawal history
GET    /api/customer/withdrawals/{id}    — Withdrawal detail

-- Merchant Products (for subscribed merchants)
GET    /api/merchant/products            — My products
POST   /api/merchant/products            — Create product
PUT    /api/merchant/products/{id}       — Update product
DELETE /api/merchant/products/{id}       — Delete product

-- Superadmin Controls
GET    /api/superadmin/toggles           — Get all feature toggles
PUT    /api/superadmin/toggles/{key}     — Toggle feature ON/OFF
GET    /api/superadmin/settings          — Get all platform settings
PUT    /api/superadmin/settings/{key}    — Update platform setting
GET    /api/superadmin/withdrawals       — All withdrawal requests
PUT    /api/superadmin/withdrawals/{id}  — Approve/reject withdrawal
```

### 15.3 Business Rules

#### Points Wallet
- Each customer has ONE wallet (global, not per-merchant)
- Balance cannot go below 0
- All transactions logged for audit
- Points expire based on platform setting (default: 12 months)

#### Redemption Rate
- Default: 100 points = RM1.00
- Superadmin can change rate
- Rate affects all NEW redemptions (not pending)
- Rate history logged in platform_settings

#### Withdrawal (REDEEM_CASH)
- Minimum withdrawal: 100 points (RM1.00)
- Maximum withdrawal: 10,000 points (RM100.00) per day
- Processing time: 1-3 business days
- Requires: Bank name, account number, account holder name
- Status: pending → approved → paid

#### Feature Toggles
- Default: REDEEM_ITEMS = ON, REDEEM_CASH = OFF
- Changes take effect immediately
- All changes logged for audit
- Users cannot see disabled features

---

## 16. Superadmin Dashboard (NEW)

### 16.1 Dashboard Features
- **Feature Toggles:** ON/OFF for VIRAL_TASKS, GIVEAWAY, REDEEM_ITEMS, REDEEM_CASH
- **Redemption Rate:** Configure points-to-RM rate
- **Withdrawal Management:** Approve/reject withdrawal requests
- **Platform Analytics:** Overview of all merchants, customers, transactions

### 16.2 Audit Trail
- All superadmin actions logged
- Timestamp, action, old_value, new_value
- Retained for compliance

---

## 17. Merchant Subscription (NEW)

### 17.1 Subscription Management
- Merchant must subscribe to access features
- Subscription via payment gateway (ToyyibPay)
- Auto-renewal or manual renewal

### 17.2 Package Tiers
| Package | Price | Products | Features |
|---------|-------|----------|----------|
| Basic | RM99/month | 10 products | Basic analytics |
| Pro | RM299/month | Unlimited | Advanced analytics |
| Enterprise | RM999/month | Unlimited | Multi-branch, API |

### 17.3 Subscription Status
- **Active:** Full access to subscribed features
- **Expired:** Limited access, cannot upload/create
- **Suspended:** No access (admin action)

---

## 18. Updated Compliance Notes

### 18.1 E-Money/BNM
- **REDEEM_ITEMS:** ✅ No BNM license needed
- **REDEEM_CASH:** ⚠️ BNM license required
- **Recommendation:** Launch with REDEEM_CASH OFF

### 18.2 Merchant Wallet
- Merchants do NOT store money in platform
- Merchants pay subscription only
- No e-money license needed

### 18.3 Points Classification
- Points = loyalty rewards (not money)
- Points non-transferable (unless superadmin enables)
- Points non-withdrawable (unless REDEEM_CASH ON)

---

**Document prepared by:** BonusHub AI Assistant
**Last updated:** 25 Ogos 2026

