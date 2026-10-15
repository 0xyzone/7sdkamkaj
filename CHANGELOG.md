# Changelog

All notable changes to the **7 Series Kamkaj** enterprise operations platform are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.1] - 2026-10-16

### Added
- **Dark Mode Brand Logo**: Integrated official white-texted Seven Series Authentic Denim logo (`public/images/logo-dark.png` and retina `@2x.png`) for dark themes.

### Changed
- **Adaptive Brand Header**: Configured `brand-logo.blade.php` and `darkModeBrandLogo()` in `AdminPanelProvider` to dynamically switch between standard black-text logo in light mode and white-text logo in dark mode.

---

## [1.5.0] - 2026-10-16

### Added
- **In-App Changelog System**: Interactive release notes viewer page (`/changelog`) with version timeline, badge categorizations (`Added`, `Changed`, `Fixed`), and mobile-responsive card layout.
- **Centralized Changelog Service**: `App\Services\ChangelogService` managing release history, semantic versioning, and feature tracking.
- **Dynamic Version Indicators**: Dynamic version linking from sidebar footer and login/auth screens to the Changelog.
- **Comprehensive Project Documentation**: Enterprise-grade GitHub `README.md` with brand assets, architecture overview, module guide, and installation steps.
- **Standardized Repository Changelog**: `CHANGELOG.md` adhering to Keep a Changelog specifications.

### Changed
- Replaced hardcoded version strings across all UI templates with dynamic references to `ChangelogService::getCurrentVersion()`.

---

## [1.4.1] - 2026-10-16

### Changed
- **Vibrant Primary Red**: Updated primary brand color to `Color::Red` for punchy, authentic denim crimson red across buttons, badges, and active navigation links.
- **Orange Danger Accent**: Swapped danger color from `Color::Rose` to `Color::Orange` to avoid visual conflict between delete/inactive actions and the brand red.

---

## [1.4.0] - 2026-10-16

### Added
- **Seven Series Brand Identity**: Integrated official brand logos and extracted primary brand Crimson Red (`#ed1c24`).
- **Responsive Brand Header**: `brand-logo.blade.php` rendering the red '7' symbol on mobile screens and full "7 SEVEN SERIES Authentic Denim" logo on desktop.
- **Progressive Web App (PWA)**: Full PWA support with `manifest.json`, Service Worker caching (`sw.js`), and cross-platform installation prompt banner (`pwa-prompt.blade.php`) for Android, iOS, and Desktop.
- **Native Mobile Card Tables**: Configured `->stackedOnMobile()` across all 5 Filament resources (`Purchases`, `PurchaseItems`, `Vendors`, `Users`, `Roles`), replacing horizontal scrolling with native mobile card views.
- **Real-Time Avatar Sync**: Custom `EditProfile` page with avatar upload and live topbar updates via `avatar-updated` browser event without requiring page reloads.
- **Compact Global Search**: Space-efficient pill search with `⌘K` badge that smoothly expands on focus.
- **Brand Favicon & App Icons**: Generated `favicon.png`, `apple-touch-icon.png`, and PWA icons (192px, 512px, maskable 512px).

---

## [1.3.0] - 2026-10-15

### Added
- **Filament Shield Enhanced**: Installed `agroezinger/filament-shield-enhanced` featuring grouped permission checkbox matrices.
- **Image Gallery Lightbox**: Integrated `al-saloul/filament-image-gallery` for interactive image lightbox previews and downloads in table listings.
- **Corporate Poppins Theme**: Custom styling in `theme.css` with Poppins typography, subtle elevations, and clean borders.
- **Profile Avatar Management**: Implemented `HasAvatar` on User model and added Dashboard `UserAvatarWidget`.
- **Password Recovery**: Enabled native Filament password reset recovery flow.
- **Storage Lifecycle Auto-Cleanup**: Automatically cleans up replaced or deleted images from storage disk across models.

---

## [1.2.0] - 2026-10-14

### Added
- **Purchases Tracking Resource**: Complete purchase orders management with invoice numbering, dates, vendor linkage, and total calculations.
- **Purchase Line Items Repeater**: Multi-item purchase lines with quantity, unit price, and automatic totals.
- **Payments Management**: Payment recording with payment modes, reference numbers, notes, and balance due tracking.
- **Slide-Over Modals**: Configured slide-over modals (`->slideOver()`) on Header `CreateAction` and Table `EditAction` across all resources.
- **Dashboard & Stats Widgets**: Statistical cards for purchases, vendor balances, and key operational metrics.

---

## [1.1.0] - 2026-10-13

### Added
- **Purchase Items Resource**: Item catalog for materials (denim fabric, buttons, zippers, thread) with codes, units, and categories.
- **In-Form Vendor Quick Creation**: `->createOptionForm()` on preferred vendor selection in purchase item form.
- **Vendor Resource**: Multi-contact repeater, PAN/VAT registration, multi-QR codes (eSewa, Khalti, Bank), and optional banking details.

---

## [1.0.2] - 2026-10-12

### Fixed
- Resolved Filament Shield roles and permissions visibility in the admin panel by registering Super Admin Gate bypass.

---

## [1.0.1] - 2026-10-11

### Changed
- Configured ntfy.sh push notification topic to `7sdkamkaj-tasks` with automated curl script (`scripts/notify.sh`).

---

## [1.0.0] - 2026-10-10

### Added
- Baseline system setup on Laravel 13 and Filament v5 with admin panel ID `admin` on `/`.
- User Resource with activation toggle, role assignment, and mobile-friendly fields.
- Role & Permission control with `bezhansalleh/filament-shield` and Spatie permissions.
- Autonomous AI agent memory rules and operational guidelines (`AGENTS.md`, `GEMINI.md`).
- Automated ntfy push notification integration.
