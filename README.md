<p align="center">
  <img src="public/images/logo.png" alt="Seven Series Authentic Denim Logo" width="320">
</p>

<h1 align="center">7 Series Kamkaj (कामकाज)</h1>

<p align="center">
  <strong>Enterprise Operations & Procurement Platform for Seven Series Authentic Denim</strong>
</p>

<p align="center">
  <a href="https://github.com/0xyzone/7sdkamkaj/releases"><img src="https://img.shields.io/badge/version-1.6.0-red.svg?style=flat-square&color=ed1c24" alt="Version 1.6.0"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/laravel-13.x-FF2D20.svg?style=flat-square&logo=laravel" alt="Laravel 13"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/filament-v5-F59E0B.svg?style=flat-square&logo=filament" alt="Filament v5"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-8.3%2B-777BB4.svg?style=flat-square&logo=php" alt="PHP 8.3+"></a>
  <a href="https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps"><img src="https://img.shields.io/badge/PWA-installable-0052CC.svg?style=flat-square" alt="PWA Installable"></a>
  <a href="https://github.com/0xyzone/7sdkamkaj/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-Proprietary-slate.svg?style=flat-square" alt="License"></a>
</p>

---

## 📌 Overview

**7 Series Kamkaj** is the dedicated Enterprise Operations Management System engineered specifically for **Seven Series Authentic Denim**. Designed from the ground up to streamline garment manufacturing procurement, factory expenses, vendor banking, material cataloging, and financial accounts, the platform delivers an app-like experience optimized for smartphones, tablets, and desktop workstations.

---

## ✨ Key Features

### 🔴 Authentic Denim Brand Identity
- **Vibrant Brand Palette**: Primary Crimson Red (`#ed1c24` / `Color::Red`) paired with a high-contrast **Orange** (`Color::Orange`) danger accent to eliminate visual conflict on destructive and overdue actions.
- **Responsive Brand Header**: Adaptive logo component rendering the red **'7'** symbol on mobile screens and the full **"7 SEVEN SERIES Authentic Denim"** banner on desktop (with white-text logo in dark mode).
- **Custom Corporate Typography**: Polished with [Poppins](https://fonts.google.com/specimen/Poppins) typography, clean micro-elevations, and dark-mode compatibility.

### 📱 Native Mobile-First Layout
- **Stacked Card Table Views**: Table rows automatically transform into native mobile cards via `->stackedOnMobile()` on small screens, completely eliminating horizontal scroll fatigue.
- **Slide-Over Modals**: Every create and edit action opens in a slide-over modal (`->slideOver()`), maintaining context without full-page reloads.
- **Compact Global Search**: Space-efficient search pill with `⌘K` badge that smoothly expands on focus, preserving topbar header room on small devices.

### 📲 Progressive Web App (PWA)
- **Installable Native Experience**: Configured with `manifest.json`, Service Worker caching (`sw.js`), and responsive app icons (192px, 512px, maskable).
- **Smart Install Prompt**: Built-in slide-up banner offering one-tap installation on Android, Chrome, and Edge, with step-by-step guidance for iOS Safari.

### 📦 Procurement & Material Catalog
- **Purchase Items**: Manage raw materials (denim fabric, buttons, rivets, zippers, labels, threads) with SKU codes, units of measurement, categories, and media.
- **In-Form Vendor Quick Add**: Create new vendors on the fly directly inside the item creation form without navigating away.
- **Comprehensive Vendor Directory**: Multi-contact repeater, PAN/VAT tax numbers, optional bank details, and multi-channel QR codes (eSewa, Khalti, Bank).

### 💰 Purchases & Payment Ledger
- **Purchase Orders**: Record purchase batches linked to vendors with line-item repeaters, unit prices, sub-totals, and purchase invoices.
- **Payment Records**: Log payments against purchase orders with payment methods, reference numbers, remarks, and automatic outstanding balance calculations.
- **Operational Dashboard Widgets**: Real-time stats for total purchase volume, outstanding vendor dues, and recent procurement activities.

### 🧾 Expenses & Cash Flow Accounting
- **Factory Expense Records**: Track factory overheads including worker salaries, factory rent, canteen groceries, machinery maintenance, utilities, and packaging.
- **Dual Accounting Logics**: Separates booked cost / liabilities from actual cash spent; an expense is only counted as real factory cash outflow once payments are logged.
- **Multi-Method Payment Ledger**: Record payments via Cash, eSewa, Khalti, Fonepay QR, Bank Transfer, Cheque, or ConnectIPS with receipt voucher uploads.
- **Quick Row Actions**: Log payments directly from table rows with automatic balance due calculation.
- **Multi-Image Attachments**: Store both bill/invoice photographs and physical purchased item/product photos with gallery lightbox previews.
- **Expense Statistics Widgets**: `ExpenseStatsOverview` and `RecentExpensesWidget` monitoring total incurred costs, real cash spent, and pending payables.

### 🛡️ Security & Role-Based Access Control
- **Filament Shield Enhanced**: Fine-grained permissions matrix with tabbed resource permission checkboxes.
- **Super Admin Bypass**: Automated Gate bypass ensuring system administrators maintain uninterrupted access.
- **Real-Time Avatar Sync**: Profile avatar upload with live browser event listener (`avatar-updated`) updating the topbar instantly without page reloads.
- **Password Recovery**: Integrated self-service password reset.

### 📜 In-App Changelog & Release Notes
- **Interactive In-App Viewer**: Dedicated `/changelog` route displaying the version timeline, categorized changes (`Added`, `Changed`, `Fixed`), and release highlights.
- **Clickable Version Indicators**: Sidebar and auth version badges link directly to the release history.
- **Standardized Documentation**: Maintained synchronously in `CHANGELOG.md` following [Keep a Changelog](https://keepachangelog.com).

---

## 🛠️ Technology Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend Framework** | [Laravel 13](https://laravel.com) (PHP 8.3+) |
| **Admin Panel** | [Filament v5](https://filamentphp.com) |
| **Permissions** | [Filament Shield](https://github.com/bezhanSalleh/filament-shield) & [Shield Enhanced](https://github.com/agroezinger/filament-shield-enhanced) |
| **Styling & Theme** | Tailwind CSS with custom Filament Admin Theme & Poppins font |
| **App Architecture** | Progressive Web App (PWA) with Service Worker caching |
| **Notifications** | Automated [ntfy.sh](https://ntfy.sh) push notification workflow |
| **Build Tools** | Vite 8, Composer, Laravel Pint |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.3 or higher with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd` or `imagick`
- Composer 2.x
- Node.js (v18+) & npm
- MySQL / MariaDB

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/0xyzone/7sdkamkaj.git
   cd 7sdkamkaj
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies & build assets**:
   ```bash
   npm install
   npm run build
   ```

4. **Environment configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database in `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=7sdkamkaj
   DB_USERNAME=root
   DB_PASSWORD=
   
   NTFY_TOPIC=7sdkamkaj-tasks
   ```

6. **Run database migrations and seeders**:
   ```bash
   php artisan migrate --seed
   ```

7. **Create storage symlink**:
   ```bash
   php artisan storage:link
   ```

8. **Serve the application**:
   ```bash
   php artisan serve
   ```
   Access the admin dashboard at `http://localhost:8000`.

---

## 🔐 Default Super Admin Credentials

| Credential | Value |
| :--- | :--- |
| **URL** | `http://localhost:8000` |
| **Email** | `admin@admin.com` |
| **Password** | `password` |

---

## 📱 PWA & Mobile Installation

1. Navigate to the panel on your mobile browser (Chrome on Android or Safari on iOS).
2. On Android/Chrome, tap the **"Install 7 Series App"** prompt banner at the bottom of the screen.
3. On iOS Safari, tap the **Share** button ($\uparrow$) and select **"Add to Home Screen"**.

---

## 🏷️ Release History

Refer to [CHANGELOG.md](CHANGELOG.md) or visit `/changelog` within the application for full release details:

- **v1.6.0** - Factory Expenses Management, Incurred vs Cash Outflow Accounting, and Widgets
- **v1.5.1** - White-Texted Brand Logo for Dark Theme
- **v1.5.0** - In-App Changelog System & Comprehensive Documentation
- **v1.4.1** - Vibrant Red primary and Orange danger color palette
- **v1.4.0** - Crimson Red brand theme, responsive logos, PWA install prompt, mobile card table views, and live avatar sync
- **v1.3.0** - Shield Enhanced, Corporate Poppins Theme, Avatars, Image Gallery, Password Reset, and Production Optimization
- **v1.2.0** - Purchases tracking, slide-over modals, mobile-first layouts, and stats widgets
- **v1.1.0** - Purchase Module with PurchaseItem and Vendor resources
- **v1.0.2** - Shield role visibility with Super Admin gate bypass
- **v1.0.1** - Notification topic update to `7sdkamkaj-tasks`
- **v1.0.0** - Initial release with User management, Filament Shield, memory rules, and ntfy notifications

---

## 📄 License & Credits

- **Proprietary**: Developed exclusively for **Seven Series Authentic Denim**.
- All brand logos, trademarks, and garment assets are the property of Seven Series Authentic Denim.
