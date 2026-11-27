# Mr. Upgrade — E-Commerce Website

> **"Don't just get a Gadget. Get an upgrade."**
> Built with PHP 7.4+ · MySQL · Vanilla JS · Pesapal Payments · Africa's Talking SMS

---

##  Quick Start (Local)

### Requirements
- WAMP / XAMPP
- PHP 7.4+
- MySQL 8.0+
- MySQL running on port **3308** (default WAMP)

### Setup Steps
1. Copy the project folder into `C:\wamp64\www\mrupgrade`
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a database named **`mrupgrade`**
4. Import: `config/setup.sql`
5. Visit: `http://localhost/mrupgrade`

---

##  Admin Credentials
| Field | Value |
|-------|-------|
| Email | `admin@gmail.com` |
| Password | *(as set by admin — change after first login)* |
| Admin URL | `http://localhost/mrupgrade/admin` |

---

##  Going Live (mrupgrade.mmuhub.com)

Open **`config/db.php`** and fill in the live values:
```php
// Already auto-detects — just fill in the placeholders:
define('DB_USER', 'YOUR_CPANEL_DB_USER');
define('DB_PASS', 'YOUR_CPANEL_DB_PASSWORD');
define('DB_NAME', 'YOUR_CPANEL_DB_NAME');
```

Open **`config/pesapal.php`** and add live keys:
```php
define('PESAPAL_KEY',    'YOUR_LIVE_PESAPAL_CONSUMER_KEY');
define('PESAPAL_SECRET', 'YOUR_LIVE_PESAPAL_CONSUMER_SECRET');
```

Open **`config/africastalking.php`** and add live keys:
```php
define('AT_USERNAME', 'your_real_at_username');
define('AT_API_KEY',  'YOUR_LIVE_AT_API_KEY');
```

Uncomment HTTPS redirect in **`.htaccess`** (lines 10–14).

---

##  Pages Reference

### Public Pages
| Page | URL |
|------|-----|
| Home | `/pages/index.php` |
| Shop | `/pages/shop.php` |
| Product Detail | `/pages/product.php?slug=...` |
| Cart | `/pages/cart.php` |
| Checkout | `/pages/checkout.php` |
| Order Confirmation | `/pages/order_confirm.php` |
| My Orders | `/pages/orders.php` |
| Installment Plan | `/pages/installment.php` |
| About | `/pages/about.php` |
| Store | `/pages/store.php` |
| Contact | `/pages/contact.php` |
| Login | `/auth/login.php` |
| Register | `/auth/register.php` |
| My Account | `/pages/profile.php` |

### Admin Panel
| Page | URL |
|------|-----|
| Dashboard | `/admin/index.php` |
| Products | `/admin/products.php` |
| Orders | `/admin/orders.php` |
| Installments | `/admin/installments.php` |
| Customers | `/admin/customers.php` |
| Messages | `/admin/messages.php` |
| My Account | `/admin/profile.php` |

---

##  Payment Integration

### Pesapal (MTN MoMo + Airtel Money)
- **Sandbox dashboard:** https://cybqa.pesapal.com/pesapalv3
- **Live dashboard:** https://pay.pesapal.com/merchant
- **IPN URL to register:** `https://mrupgrade.mmuhub.com/payments/pesapal_ipn.php`
- **Callback URL:** `https://mrupgrade.mmuhub.com/payments/pesapal_callback.php`

### Africa's Talking (SMS)
- **Dashboard:** https://account.africastalking.com
- **Sandbox simulator:** https://simulator.africastalking.com
- Current environment: Sandbox (switch to live in `config/africastalking.php`)

---

##  Security Features
- CSRF tokens on all forms
- Prepared statements (no SQL injection)
- Login rate limiting (5 attempts / 15 min)
- Session hardening (HttpOnly, SameSite, regeneration)
- Security headers (X-Frame, XSS, MIME)
- File upload MIME validation
- .htaccess protection (config dir, uploads, sensitive files)

---

##  SEO
- Meta tags + Open Graph on every page
- Product JSON-LD structured data
- Local Business JSON-LD
- Dynamic XML sitemap: `/sitemap.php`
- robots.txt configured
- After going live: submit sitemap to Google Search Console

---

##  Project Structure
```
mrupgrade/
├── admin/              Admin panel pages
├── assets/             CSS, JS, images
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
├── auth/               Login, register, logout
├── config/             All configuration files
│   ├── db.php          ← DB + environment detection
│   ├── pesapal.php     ← Payment gateway
│   ├── africastalking.php ← SMS
│   ├── security.php    ← Security helpers
│   └── seo.php         ← SEO helpers
├── includes/           header.php, footer.php
├── pages/              All public pages
├── payments/           Pesapal IPN + callback
├── uploads/products/   Admin-uploaded product images
├── .htaccess           Production security rules
├── robots.txt          Search engine crawler rules
├── sitemap.php         Dynamic XML sitemap
└── README.md           This file
```

---

##  Tech Stack
| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4 |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3, Vanilla JS |
| Fonts | Google Fonts (Inter) |
| Icons | Font Awesome 6 |
| Payments | Pesapal API v3 |
| SMS | Africa's Talking API |
| Server (local) | WAMP (Apache 2.4) |
| Server (live) | cPanel / Apache |

---

##  Before Going Live Checklist
- [ ] Fill in live DB credentials in `config/db.php`
- [ ] Add live Pesapal keys in `config/pesapal.php`
- [ ] Add live AT keys in `config/africastalking.php`
- [ ] Uncomment HTTPS redirect in `.htaccess`
- [ ] Register IPN URL in Pesapal dashboard
- [ ] Import `config/setup.sql` into live DB
- [ ] Set `uploads/products/` folder permissions to 755
- [ ] Delete `test_at.php`, `test_pesapal.php`, `test_sms.php`
- [ ] Submit sitemap to Google Search Console
- [ ] Activate SSL on cPanel (Let's Encrypt — free)
- [ ] Change admin password on live site

---

*Built for Mr. Upgrade, Kampala Uganda — Pioneer Mall, Room PG52*
