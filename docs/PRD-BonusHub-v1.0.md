# BonusHub — Product Requirements Document (PRD)

**Version:** 1.1
**Date:** 28 Ogos 2026
**Author:** Marz Technology & Trading
**Status:** Live (bonushub.my)

---

## 1. Executive Summary

BonusHub ialah platform SaaS loyalty & rewards program untuk bisnes Malaysia. Platform ini membantu merchant meningkatkan customer retention melalui sistem mata ganjaran (points), tier keahlian, dan program referral. BonusHub juga menyediakan modul viral loop dan giveaway untuk customer acquisition.

**Unique Selling Proposition (USP):**
- Satu-satunya platform Malaysia yang gabungkan loyalty + viral loop + giveaway dalam satu SaaS
- Merchant-centric (bukan platform task marketplace macam TaskReward)
- Points-based (tiada cash deposit, elak isu judi/loteri)
- Compliant dengan undang-undang Malaysia & prinsip Islam
- Model hybrid tracking: auto (unique link + UTM) + honor system + screenshot proof

---

## 2. Product Vision

**Visi:** Menjadi platform loyalty #1 untuk bisnes kecil & sederhana (SME) di Malaysia.

**Misi:** Merchant boleh setup loyalty program dalam 5 minit, dan customer terus engage dengan brand.

**3-Pillar Growth Engine:**

| Pillar | Fungsi | Model |
|--------|--------|-------|
| 🎖️ Royalty Module | Core loyalty — points, tiers, redemption | Merchant-centric loyalty program |
| 🔄 Viral Loop | Growth hacking — social tasks, referral | TaskReward-inspired viral growth |
| 🎁 Giveaway | Campaign-based referral contests | KingSumo-inspired viral campaigns |

---

## 3. Target Users

### 3.1 Merchant (B2B)
- **Target:** F&B, retail, service business di Malaysia
- **Size:** SME 1-50 staff
- **Need:** Tambahan customer retention, kurangkan churn
- **Pain point:** Susah nak track customer loyalty manual, takde system yang murah & mudah

### 3.2 Customer (End User)
- **Target:** Pengguna mobile-first Malaysia
- **Behaviour:** Suka rewards, suka share deals dengan kawan
- **Need:** Senang nak collect & redeem points tanpa app khas

### 3.3 Admin (BonusHub Team)
- **Need:** Monitor platform health, handle support, manage billing

---

## 4. Module Architecture — ROYALTY ENGINE (Sedia Ada — Enhanced)

### 4.1 Status Sedia Ada

**Database Tables:**
- `packages` — Pricing tiers (branch_limit, staff_limit, giveaway_limit, task_limit)
- `merchants` — Merchant profile (company_name, logo, phone, address, status, package_id)
- `branches` — Merchant branches (name, address, phone, status)
- `customers` — Customer profile (name, phone, email, tier_global, birthdate)
- `customer_merchant` — Pivot (customer_id, merchant_id, points, tier_per_merchant, tied_at, campaign_link_id)
- `loyalty_rates` — Per-merchant rates (rate_per_rm, earn_rate, redeem_rate, min_redeem, max_redeem, festive_multiplier, product_specific_rules)
- `merchant_rewards` — Reward catalog (name, description, points_required, stock_quantity, stock_left, claim_type, delivery_cost, delivery_fee, download_url, access_code_prefix, status, image)
- `points_transactions` — Points ledger (customer_id, merchant_id, branch_id, staff_id, type[earn/redeem/void/expired], points, amount_spent, status[pending/approved/rejected], approved_by, notes)
- `redemptions` — Redemption records (customer_id, merchant_id, reward_id, points_used, cash_topup, claim_method, status[pending/approved/rejected/completed/cancelled], claim_code, staff_id)
- `merchant_tiers` — Tier configuration (tier_name, min_points)
- `promos` — Promotions (type[registration_bonus/multiplier/fixed_bonus], value, starts_at, ends_at)
- `campaign_links` — Referral tracking (name, slug, medium, visits, registrations, status, expires_at)
- `notification_settings` — Merchant notification preferences
- `in_app_notifications` — Push notification records

**Features Sedia Ada:**
- Merchant dashboard (stats, customer list, leaderboard, liability report)
- Staff management (add staff, assign branch)
- Customer points earn/redeem
- Reward catalog (4 claim types: self_collect, delivery, download, access_code)
- Tier system (configurable per merchant)
- Campaign links (unique referral URLs with visit/registration tracking)
- Promo engine (registration bonus, multiplier, fixed bonus)
- Customer app (profile, points, rewards, leaderboard, join merchant)
- Pending approvals workflow
- Branch management

### 4.2 Features BARU Perlu Dibangunkan

#### 4.2.1 Enhanced Points Earning
- **QR Code Scan:** Customer scan QR merchant untuk earn points
- **Receipt Upload:** Customer upload resit, staff approve
- **Birthday Bonus:** Auto-points on customer birthday
- **Streak Bonus:** Points bonus untuk consecutive visits
- **Referral Points:** Customer earn points when referring friends

#### 4.2.2 Enhanced Redemption
- **Voucher Code:** Generate unique voucher codes
- **Digital Rewards:** E-book, coupon codes, access codes
- **Physical Rewards:** Self-collect or delivery
- **Points Transfer:** Customer boleh transfer points ke kawan (merchant-configurable)
- **Points Expiry:** Auto-expire points after X months

#### 4.2.3 Advanced Analytics
- **Customer Segmentation:** RFM analysis (Recency, Frequency, Monetary)
- **Churn Prediction:** Flag at-risk customers
- **Revenue Attribution:** Track revenue per campaign
- **Cohort Analysis:** Customer retention over time

#### 4.2.4 Multi-Branch Support
- **Centralized Points:** Points valid across all branches
- **Branch-Specific Rewards:** Some rewards only at certain branches
- **Cross-Branch Redemption:** Customer boleh redeem di branch lain

---

## 5. Module Architecture — VIRAL ENGINE (NEW)

### 5.1 Konsep

Viral Engine membenarkan merchant mencipta social tasks untuk customer. Customer complete tasks (like, follow, share, review) dan earn points sebagai ganjaran. Inspired by TaskReward tetapi merchant-centric (bukan marketplace terbuka).

### 5.2 Key Differences dari TaskReward

| Aspek | TaskReward | BonusHub Viral Engine |
|-------|-----------|----------------------|
| Model | Open marketplace (vendor bayar) | Merchant-centric (merchant create tasks) |
| Payment | Cash deposit + cash reward | Points-based (tiada cash) |
| Workers | Random public workers | Merchant's own customers |
| Anti-fraud | Screenshot + Gemini AI | Hybrid: auto-track + honor system + screenshot |
| Revenue | 68% platform margin | Points liability on merchant |

### 5.3 Features

#### 5.3.1 Task Creation (Merchant Side)
- **Task Types:**
  - Social Media Follow (Instagram, TikTok, Facebook, Twitter, YouTube)
  - Social Media Like/Comment/Share
  - Google Review
  - Visit Store (GPS check-in)
  - Attend Event
  - Custom Task (merchant-defined)

- **Task Configuration:**
  - Points reward per task
  - Deadline / expiry
  - Max participants
  - Verification method (auto-track / honor system / screenshot proof)
  - Task description & instructions
  - Required proof (screenshot upload, link submission)

#### 5.3.2 Task Execution (Customer Side)
- **Task Discovery:** Browse available tasks in customer app
- **Task Submission:**
  - Auto-tracking: Unique referral link generated
  - Honor system: Checkbox "I've completed this task"
  - Screenshot proof: Upload screenshot for verification
- **Status Tracking:** Pending → Verified → Points Awarded
- **Points Credit:** Auto on verification

#### 5.3.3 Verification System (Hybrid)

**Tier 1 — Auto-Track (Low Effort):**
- Unique referral link per customer per task
- UTM parameter tracking
- Auto-detect clicks/conversions via link
- Works for: Follow, Visit, Sign-up

**Tier 2 — Honor System (Medium Effort):**
- Customer checks box "I've completed this task"
- Merchant dashboard shows completion stats
- Flag suspicious patterns (too many completions, too fast)
- Works for: Like, Comment, Share

**Tier 3 — Screenshot Proof (High Value):**
- Customer uploads screenshot as proof
- Staff reviews in merchant dashboard
- Approve/reject with notes
- Works for: High-value tasks (>100 points)

#### 5.3.4 Anti-Fraud Measures
- **IP Tracking:** Flag multiple tasks from same IP
- **Completion Velocity:** Flag tasks completed too quickly
- **Pattern Detection:** Flag users with abnormally high completion rate
- **Manual Review:** Staff can review any suspicious submission
- **Rate Limiting:** Max tasks per day per customer
- **Unique Verification:** Each task can only be completed once per customer

#### 5.3.5 Merchant Dashboard (Viral)
- **Task List:** View all active tasks with stats
- **Submission Queue:** Review pending submissions
- **Analytics:** Task completion rate, reach, engagement
- **ROI Calculator:** Points spent vs estimated reach

### 5.4 Database Schema (BARU)

```
viral_tasks
- id, merchant_id, name, description, task_type, points_reward
- verification_method (auto_track / honor_system / screenshot_proof)
- deadline, max_participants, max_per_customer
- status (active / paused / completed / expired)
- created_at, updated_at

viral_task_submissions
- id, task_id, customer_id
- status (pending / verified / rejected / expired)
- proof_type (auto / checkbox / screenshot)
- proof_url, proof_notes
- verified_by, verified_at
- rejection_reason
- points_awarded
- created_at, updated_at

viral_task_links
- id, task_id, customer_id
- unique_slug, utm_source, utm_medium, utm_campaign
- clicks, conversions
- created_at

viral_task_analytics
- id, task_id, date, views, clicks, submissions, verified, points_spent
```

---

## 6. Module Architecture — GIVEAWAY ENGINE (NEW)

### 6.1 Konsep

Giveaway Engine membenarkan merchant mencipta campaign giveaway/referral contest. Customer join campaign, invite kawan, dan earn entries untuk menang hadiah. Inspired by KingSumo tetapi points-based dan compliant.

### 6.2 Key Differences dari KingSumo

| Aspek | KingSumo | BonusHub Giveaway Engine |
|-------|---------|------------------------|
| Model | Email collection + giveaway | Points-based referral contest |
| Prize | Random draw | Merit-based (most referrals wins) |
| Winner | Random selection | Top referrers / threshold-based |
| Legal | Grey area (sweepstakes) | Compliant (contest, bukan lottery) |
| Platform | Standalone | Integrated with loyalty |

### 6.3 Features

#### 6.3.1 Campaign Creation (Merchant Side)
- **Campaign Types:**
  - Referral Contest: Most referrals wins
  - Milestone Giveaway: Reach X referrals = win
  - Flash Giveaway: Time-limited campaign
  - Seasonal Campaign: Hari Raya, Chinese New Year, etc.

- **Campaign Configuration:**
  - Campaign name & description
  - Prize(s) — reward from merchant reward catalog OR custom prize
  - Duration (start/end date)
  - Referral goal (X referrals to win)
  - Winner count (how many winners)
  - Bonus points for participation
  - Sharing templates (pre-written social posts)

#### 6.3.2 Customer Participation
- **Join Campaign:** One-click join from customer app
- **Referral Link:** Unique link generated per customer
- **Sharing:** Share via WhatsApp, Facebook, Instagram, Twitter, Telegram
- **Progress Tracking:** See how many referrals, current ranking
- **Leaderboard:** Real-time ranking of top referrers

#### 6.3.3 Winner Selection (Compliant)

**Model: Contest (bukan Lottery)**
- ❌ NO random draw / cabutanrawak
- ❌ NO purchase required
- ✅ Merit-based: Top referrers wins
- ✅ Threshold-based: Reach X referrals = guaranteed prize
- ✅ All participants earn participation points

**Selection Methods:**
1. **Top N Referrers:** Customer with most referrals wins
2. **Threshold Winners:** Anyone who reaches X referrals wins
3. **Milestone Tiers:** 5 referrals = bronze, 10 = silver, 25 = gold (different prizes)

#### 6.3.4 Fraud Prevention
- **Referral Validation:** New referral must register + activate
- **Duplicate Detection:** Flag same device/IP referrals
- **Minimum Engagement:** Referral must be active for X days
- **Admin Review:** Merchant can review suspicious referrals

#### 6.3.5 Campaign Analytics
- **Real-time Dashboard:** Live stats (participants, referrals, reach)
- **Referral Funnel:** Views → Clicks → Registrations → Active
- **Social Media Breakdown:** Which platforms driving referrals
- **ROI Metrics:** Cost per referral, points spent vs reach
- **Winner Announcement:** Auto-announce winners in app

### 6.4 Database Schema (BARU)

```
giveaway_campaigns
- id, merchant_id, name, description
- campaign_type (referral_contest / milestone / flash / seasonal)
- prize_type (reward_catalog / custom)
- prize_reward_id (nullable), prize_description, prize_image
- start_at, end_at
- referral_goal (int, nullable)
- winner_count (int, default 1)
- participation_points (int, default 0)
- max_participants (nullable)
- status (draft / active / completed / cancelled)
- created_at, updated_at

giveaway_entries
- id, campaign_id, customer_id
- referral_count (int, default 0)
- rank (nullable, set after campaign ends)
- status (participating / winner / completed)
- won_at, claimed_at
- created_at, updated_at

giveaway_referrals
- id, campaign_id, referrer_id (customer), referred_id (customer)
- status (pending / valid / invalid)
- validated_at, invalid_reason
- created_at

giveaway_shares
- id, campaign_id, customer_id
- platform (whatsapp / facebook / instagram / twitter / telegram / other)
- share_url, clicks
- created_at
```

---

## 7. Cross-Module Integration

### 7.1 Points Flow
```
Customer buat purchase → Merchant scan QR → Points earned (Royalty)
Customer complete viral task → Points earned (Viral)
Customer join giveaway → Participation points earned (Giveaway)
Customer redeem reward → Points deducted (Royalty)
```

### 7.2 Tier Progression
```
Points accumulated → Tier upgrade check → Auto-promotion
Viral task points → Count towards tier
Giveaway points → Count towards tier
```

### 7.3 Notification System
```
Points earned → Push notification
Reward redeemed → Push notification
Tier upgraded → Push notification + special message
Giveaway ending soon → Reminder
Viral task available → Push notification
```

---

## 8. User Stories

### 8.1 Merchant Stories
- As a merchant, I want to create a loyalty program in 5 minutes so I can start rewarding customers immediately.
- As a merchant, I want to set different points rates for different products so I can control my loyalty budget.
- As a merchant, I want to create viral tasks so my customers promote my business on social media.
- As a merchant, I want to run giveaway campaigns so I can rapidly grow my customer base.
- As a merchant, I want to see real-time analytics so I can measure ROI.
- As a merchant, I want to review task submissions so I can prevent fraud.

### 8.2 Customer Stories
- As a customer, I want to earn points when I make purchases so I get rewarded for loyalty.
- As a customer, I want to redeem points for rewards so I get tangible value.
- As a customer, I want to see my tier and progress so I'm motivated to earn more.
- As a customer, I want to complete viral tasks so I can earn extra points.
- As a customer, I want to join giveaway campaigns so I can win prizes.
- As a customer, I want to share referral links so I can earn points when friends join.

### 8.3 Admin Stories
- As an admin, I want to monitor platform health so I can ensure uptime.
- As an admin, I want to manage merchant subscriptions so I can handle billing.
- As an admin, I want to view platform-wide analytics so I can make business decisions.

---

## 9. Business Rules

### 9.1 Points Rules
- Points are merchant-specific (tidak boleh cross-merchant redeem)
- Points expiry: Configurable per merchant (default 12 months)
- Minimum redemption: Configurable per merchant
- Maximum redemption per transaction: Configurable per merchant
- Points transfer between customers: Optional, merchant-configurable

### 9.2 Tier Rules
- Tier upgrade: Based on total accumulated points
- Tier downgrade: Optional, configurable (time-based or points-based)
- Tier benefits: Set by merchant (discount %, bonus points, exclusive rewards)

### 9.3 Viral Task Rules
- Each customer can complete each task once
- Task expiry: Configurable per task
- Points awarded only after verification
- Merchant can reject submissions with reason
- Rate limiting: Max tasks per day (configurable)

### 9.4 Giveaway Rules
- No random draw — merit-based only
- Winner selection: Top referrers or threshold-based
- Referral must be validated (not fake accounts)
- Campaign duration: Min 1 day, max 90 days
- Participation is free — no purchase required

### 9.5 Compliance Rules
- NO cash rewards — points only
- NO random selection for prizes
- NO purchase required for giveaway entry
- All campaigns must have clear T&C
- Data protection: PDPA compliant
- Islamic compliance: No gambling, no riba, no gharar

---

## 10. Competitive Analysis Summary

### 10.1 TaskReward (taskreward.cc)
- **Model:** Task marketplace (vendor bayar, worker earn cash)
- **Pricing:** Vendor RM0.60-RM1,800, TaskReward ambil 68% margin
- **Rate:** Flat rate RM0.03/task (58% services), effective RM1.80-3.00/jam
- **Fraud:** Screenshot + Gemini AI + IP tracking + manual review (100%)
- **Anti-fraud gap:** Tiada automated unfollow detection
- **Weakness:** Model deposit cash = isu compliance Malaysia, tiada loyalty system

### 10.2 KingSumo
- **Model:** Referral-based giveaway campaign
- **Winner selection:** Merit-based (most referrals wins)
- **Sharing:** WhatsApp, Facebook, Twitter
- **Weakness:** Standalone, tiada integration dengan loyalty

### 10.3 BonusHub Differentiation
- ✅ Loyalty + Viral + Giveaway dalam satu platform
- ✅ Points-based (bukan cash) — compliant
- ✅ Merchant-centric (bukan marketplace terbuka)
- ✅ Auto-tracking + honor system + screenshot hybrid
- ✅ Malaysian market focus (Bahasa Malaysia, local payment)

---

## 11. Success Metrics

### 11.1 Merchant Metrics
- Number of active merchants
- Average points issued per merchant
- Merchant retention rate (monthly)
- Average revenue per merchant (ARPM)

### 11.2 Customer Metrics
- Total registered customers
- Monthly active customers (MAC)
- Average points per customer
- Redemption rate (points redeemed / points issued)
- Viral task completion rate
- Giveaway participation rate

### 11.3 Viral Metrics
- Viral coefficient (K-factor): referrals per customer
- Customer acquisition cost (CAC)
- Organic growth rate
- Campaign ROI

### 11.4 Platform Metrics
- Uptime (target: 99.9%)
- API response time (target: <200ms p95)
- Support ticket resolution time

---



---

## 13. POINTS WALLET SYSTEM (NEW)

### 13.1 Konsep
Setiap customer mempunyai Points Wallet yang menyimpan semua points yang diperolehi. Points boleh ditebus untuk mendapatkan barangan dari merchant partner ATAU ditukar kepada wang tunai untuk withdrawal ke bank.

### 13.2 Points Earning
- **Viral Tasks:** Customer complete task → earn points
- **Giveaway Participation:** Customer join campaign → earn participation points
- **Merchant Transactions:** (Fasa akan datang) Customer buat pembelian → earn points

### 13.3 Points Redemption Options

#### Option 1: Redeem Items (Merchant Partner)
- Customer pilih item dari merchant partner
- Points ditolak mengikut harga item
- Item dikirim atau self-collect

#### Option 2: Convert to Cash & Withdraw
- Customer tukar points ke wang tunai
- Wang dimasukkan ke bank account
- **Nota:** Features ini PERLU lesen BNM (e-money)

### 13.4 Redemption Rate
- **Default:** 100 points = RM1.00
- **Superadmin Control:** Rate boleh ditukar oleh superadmin
- **Rate Affects:** Semua redemption selepas perubahan

---

## 14. SUPERADMIN CONTROLS (NEW)

### 14.1 Feature Toggles
Superadmin boleh mengaktifkan/menyahaktifkan features berikut:

| Feature | Toggle | Effect |
|---------|--------|--------|
| VIRAL_TASKS | ON/OFF | User boleh/nampak viral tasks |
| GIVEAWAY | ON/OFF | User boleh/nampak giveaway campaigns |
| REDEEM_ITEMS | ON/OFF | User boleh redeem points untuk items |
| REDEEM_CASH | ON/OFF | User boleh convert points ke cash |

### 14.2 User Experience
- **User TIDAK ada option ON/OFF** — user hanya nampak & guna apa yang available
- **Jika dua-dua method ON** — user boleh pilih nak redeem items ATAU convert to cash
- **Jika satu method OFF** — user hanya nampak method yang ON

### 14.3 Redemption Rate Control
- Superadmin boleh tukar rate (cth: 100 pts = RM1)
- Rate affects semua redemption selepas perubahan
- Rate history logged untuk audit

---

## 15. MERCHANT MODULE (NEW)

### 15.1 Merchant Subscription
- Merchant kena **beli pakej** untuk akses merchant features
- Merchant **TIDAK simpan duit** dalam platform
- Subscription = akses features sahaja

### 15.2 Merchant Features (After Subscribe)
- **Upload Products:** Merchant boleh upload items untuk customer redeem
- **Manage Products:** Edit, delete, set pricing (points)
- **View Dashboard:** Lihat redemption stats, customer data
- **Create Tasks:** Create viral tasks untuk customers

### 15.3 Merchant Packages
| Package | Price | Features |
|---------|-------|----------|
| Basic | RM99/month | Upload 10 products, basic analytics |
| Pro | RM299/month | Unlimited products, advanced analytics |
| Enterprise | RM999/month | Multi-branch, API access |

### 15.4 Non-Subscribed Merchants
- **TIDAK boleh** access merchant features
- **TIDAK boleh** upload products
- **HANYA nampak** basic info sahaja

### 15.5 Registration & Verification Flow (IMPLEMENTED — 28 Aug 2026)

#### Customer Registration
1. User akses `/register`
2. Masukkan: nama, email, telefon, password
3. Tekan "Request Code" — OTP 6 digit dihantar ke email
4. Masukkan OTP, tekan "Verify"
5. Jika OTP valid → proceed ke step 3 (isi maklumat)
6. Submit → User + Customer record dicipta → redirect ke dashboard

#### Merchant Registration
1. Merchant akses `/merchant/register`
2. Masukkan: nama syarikat, nama pemilik, email, telefon, password
3. Tekan "Request Code" — OTP 6 digit dihantar ke email
4. Masukkan OTP, tekan "Verify"
5. Submit → User + Merchant record dicipta → redirect ke verification page

#### Merchant Verification (IC & SSM Upload)
1. Merchant nampak halaman `/merchant/verification`
2. Pilihan:
   - **Upload IC & SSM:** Muat naik gambar IC dan SSM → status `pending_approval`
   - **Skip:** Tekan "Skip" → status kekal `pending_verification` (limited access)
3. Merchant boleh akses dashboard selepas skip, tetapi menu terhad

#### Superadmin Approval
1. Superadmin nampak badge "Merchant Menunggu" di sidebar
2. Klik `/superadmin/merchants-pending` → senarai merchant menunggu
3. Review dokumen (IC & SSM) atau tiada dokumen (skip)
4. Pilihan:
   - **Approve:** Status → `active`, email notifikasi dihantar
   - **Reject:** Status → `rejected`, email dengan sebab dihantar
5. Merchant yang telah diluluskan boleh akses semua features

#### Status Flow
```
pending_verification → Upload IC/SSM → pending_approval → Superadmin approve → active
pending_verification → Skip        → pending_verification → Superadmin approve → active
```

#### Anti-Bot / Security
- OTP e-mel wajib sebelum boleh daftar (anti-bot)
- CheckMerchantApproved middleware: merchant yang belum diluluskan tidak boleh akses merchant features
- Honeypot + time trap anti-bot pada borang pendaftaran
- CSRF token wajib untuk semua POST requests

---

## 16. UPDATED COMPLIANCE NOTES

### 16.1 E-Money/BNM Compliance
- **REDEEM_ITEMS:** ✅ TIDAK perlu lesen BNM (loyalty points)
- **REDEEM_CASH:** ⚠️ PERLU lesen BNM (e-money provider)
- **Cadangan:** Mulakan dengan REDEEM_CASH OFF, enable lepas dapat lesen

### 16.2 Merchant Wallet
- **Merchant TIDAK simpan duit** dalam platform
- **Merchant bayar subscription** sahaja
- **TIDAK perlu** lesen e-money untuk merchant module

### 16.3 Points Classification
- Points = loyalty rewards (bukan wang)
- Points tidak boleh dipindahkan ke orang lain (unless superadmin ON)
- Points tidak boleh ditebus ke wang (kecuali REDEEM_CASH ON)

---

## 17. FLOW DIAGRAMS (UPDATED)

### 17.1 Points Wallet Flow
```
User Complete Task
       │
       ▼
Points Earned (100 pts)
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│                    POINTS WALLET                        │
│                                                         │
│  Points: 100 pts = RM1.00                             │
│                                                         │
│  ┌─────────────────┐    ┌─────────────────┐           │
│  │ REDEEM ITEMS    │    │ REDEEM CASH     │           │
│  │ (Default: ON)   │    │ (Default: OFF)  │           │
│  │                 │    │                 │           │
│  │ Free Coffee     │    │ RM1.00 withdraw │           │
│  │ Merchandise     │    │ to bank         │           │
│  └─────────────────┘    └─────────────────┘           │
│                                                         │
│  ⚠️ REDEEM CASH boleh di-enable/disable oleh superadmin │
└─────────────────────────────────────────────────────────┘
```

### 17.2 Superadmin Control Flow
```
Superadmin Login
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│                    SUPERADMIN DASHBOARD                 │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  FEATURE TOGGLES                               │   │
│  │                                                 │   │
│  │  VIRAL_TASKS     — ON/OFF                     │   │
│  │  GIVEAWAY        — ON/OFF                     │   │
│  │  REDEEM_ITEMS    — ON/OFF                     │   │
│  │  REDEEM_CASH     — ON/OFF                     │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  REDEMPTION RATE                               │   │
│  │                                                 │   │
│  │  Points to RM Rate — 50-500 pts per RM1       │   │
│  │  (Default: 100 pts = RM1)                     │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 17.3 Merchant Subscription Flow
```
Merchant Register
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│                    MERCHANT DASHBOARD                   │
│                                                         │
│  ❌ BEFORE SUBSCRIBE:                                  │
│  • TIDAK boleh upload products                         │
│  • TIDAK boleh create tasks                            │
│  • HANYA nampak basic info                             │
│                                                         │
│  ✅ AFTER SUBSCRIBE:                                   │
│  • BOLEH upload products                               │
│  • BOLEH create tasks                                  │
│  • BOLEH view analytics                                │
│  • BOLEH manage redemption                             │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  PACKAGES:                                     │   │
│  │  • Basic: RM99/month (10 products)             │   │
│  │  • Pro: RM299/month (unlimited)                │   │
│  │  • Enterprise: RM999/month (multi-branch)      │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 18. DATABASE SCHEMA (UPDATED)

### 18.1 New Tables

```sql
-- Points Wallet
points_wallets
- id, customer_id, balance (bigint), total_earned (bigint)
- total_redeemed (bigint), created_at, updated_at

-- Points Transactions
points_transactions
- id, wallet_id, type (earn/redeem/expire/transfer)
- amount (bigint), balance_after (bigint)
- reference_type, reference_id (polymorphic)
- description, created_at

-- Withdrawal Requests (for REDEEM_CASH)
withdrawal_requests
- id, customer_id, wallet_id
- points_amount (bigint), rm_amount (decimal)
- status (pending/approved/rejected/paid)
- bank_name, bank_account, bank_holder
- approved_by, approved_at, paid_at
- rejection_reason, created_at, updated_at

-- Merchant Products
merchant_products
- id, merchant_id, name, description
- image_url, points_price (bigint)
- stock_quantity, status (active/inactive)
- created_at, updated_at

-- Feature Toggles
feature_toggles
- id, key (unique), value (boolean)
- label, description
- updated_by, updated_at

-- Platform Settings
platform_settings
- id, key (unique), value (text)
- type (string/integer/boolean/json)
- label, description
- updated_by, updated_at
```

### 18.2 Updated Tables

```sql
-- Add wallet_id to submissions
viral_task_submissions
- ADD wallet_id (foreign key to points_wallets)

-- Add wallet_id to giveaway entries
giveaway_entries
- ADD wallet_id (foreign key to points_wallets)
```


---

## 12. Roadmap

### Phase 1 — Foundation (Bulan 1-2) ✅
- [x] Royalty Engine (points, tiers, redemption, rewards catalog)
- [x] Merchant dashboard (stats, customer list, leaderboard, reports)
- [x] Staff module (customer lookup, add points, redeem, void)
- [x] Customer app (profile, points, rewards, leaderboard, join merchants)
- [x] Campaign links (unique referral URLs with tracking)
- [x] Promo engine (registration bonus, multiplier, fixed bonus)
- [x] Branch management (multi-branch support)
- [x] Merchant registration with OTP email verification
- [x] Merchant IC/SSM document verification
- [x] Superadmin merchant approval workflow (approve/reject)
- [x] CheckMerchantApproved middleware (access control)
- [x] HTTPS deployment at bonushub.my
- [ ] QR code points earning
- [ ] Customer mobile web app (PWA)
- [ ] Basic analytics dashboard

### Phase 2 — Viral Engine (Bulan 3-4)
- [ ] Viral task creation (merchant side)
- [ ] Task execution (customer side)
- [ ] Auto-tracking (unique links + UTM)
- [ ] Screenshot proof submission
- [ ] Task verification workflow
- [ ] Task analytics

### Phase 3 — Giveaway Engine (Bulan 5-6)
- [ ] Campaign creation (merchant side)
- [ ] Referral link generation
- [ ] Leaderboard
- [ ] Winner selection (merit-based)
- [ ] Campaign analytics
- [ ] Social sharing integration

### Phase 4 — Growth & Optimization (Bulan 7+)
- [ ] Advanced analytics (RFM, churn prediction)
- [ ] A/B testing for campaigns
- [ ] API for third-party integrations
- [ ] White-label solution
- [ ] Mobile app (React Native)

---

**Document prepared by:** BonusHub AI Assistant
**Last updated:** 28 Ogos 2026
