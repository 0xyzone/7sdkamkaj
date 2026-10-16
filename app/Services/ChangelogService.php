<?php

namespace App\Services;

class ChangelogService
{
    public const CURRENT_VERSION = '1.6.0';

    public static function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getReleases(): array
    {
        return [
            [
                'version' => '1.6.0',
                'date' => '2026-10-16',
                'title' => 'Factory Expenses Management & Cash Flow Accounting',
                'type' => 'minor',
                'badge' => 'New Module',
                'badge_color' => 'primary',
                'highlights' => 'Introduced the Expenses module for tracking factory expenditures (salary, rent, groceries, maintenance, utilities) with dual incurred vs actual cash outflow accounting, payment logs, and stats widgets.',
                'changes' => [
                    'added' => [
                        'New `Expenses` category featuring `ExpenseRecordResource` and `ExpenseTypeResource`.',
                        '`ExpenseRecord` model with auto-numbering (`EXP-YYYYMM-XXXX`), optional quantity and unit measurement, bill/invoice and item photo uploads.',
                        'Payment transactions repeater with multi-method payment logging (Cash, eSewa, Khalti, QR, Bank Transfer, Cheque).',
                        'Quick "Log Payment" slide-over table action for direct payment settlement from table rows.',
                        'Auto-calculation of paid vs due amounts and payment settlement status (`unpaid`, `partial`, `paid`).',
                        '`ExpenseStatsOverview` widget showing total incurred liabilities, actual cash spent, and outstanding unpaid dues.',
                        '`RecentExpensesWidget` added to the admin dashboard.',
                        'Seeded 9 standard factory expense categories: Salary, Rent, Groceries, Office Supplies, Machinery Maintenance, Utilities, Logistics, Packaging, and Misc.',
                        'Generated Filament Shield permissions and policies for expense resources.',
                    ],
                ],
            ],
            [
                'version' => '1.5.1',
                'date' => '2026-10-16',
                'title' => 'White-Texted Brand Logo for Dark Theme',
                'type' => 'patch',
                'badge' => 'UI Polish',
                'badge_color' => 'warning',
                'highlights' => 'Integrated official white-texted Seven Series Authentic Denim logo for dark mode environments.',
                'changes' => [
                    'added' => [
                        'Added `public/images/logo-dark.png` and retina `@2x.png` for dark theme rendering.',
                    ],
                    'changed' => [
                        'Updated `brand-logo.blade.php` to dynamically toggle between the standard black-text logo in light mode and the white-text logo in dark mode.',
                        'Configured `darkModeBrandLogo()` in `AdminPanelProvider` for dark theme brand asset synchronization.',
                    ],
                ],
            ],
            [
                'version' => '1.5.0',
                'date' => '2026-10-16',
                'title' => 'In-App Changelog System & Comprehensive Documentation',
                'type' => 'minor',
                'badge' => 'New Feature',
                'badge_color' => 'primary',
                'highlights' => 'Added in-app interactive release notes viewer, dynamic version badges, comprehensive GitHub documentation, and standardized CHANGELOG.md.',
                'changes' => [
                    'added' => [
                        'In-App Changelog page (`/changelog`) with version timeline, badge categorizations, and mobile card views.',
                        'Clickable version badges in sidebar footer and auth screens linking directly to system release history.',
                        'Centralized `ChangelogService` managing release history, semantic versioning, and feature tracking.',
                        'Comprehensive GitHub `README.md` complete with brand asset showcases, architecture breakdown, module docs, mobile UX guide, and setup instructions.',
                        'Standardized `CHANGELOG.md` adhering to Keep a Changelog specifications.',
                    ],
                    'changed' => [
                        'Dynamic version referencing across all UI components replacing static hardcoded version strings.',
                    ],
                ],
            ],
            [
                'version' => '1.4.1',
                'date' => '2026-10-16',
                'title' => 'Vibrant Red & Orange Danger Color Palette',
                'type' => 'patch',
                'badge' => 'UI Polish',
                'badge_color' => 'warning',
                'highlights' => 'Replaced muted hex generation with vibrant brand red and high-contrast orange for danger actions.',
                'changes' => [
                    'changed' => [
                        'Set primary brand color to `Color::Red` for punchy, authentic denim crimson red across buttons, badges, and active links.',
                        'Swapped danger color from `Color::Rose` to `Color::Orange` to prevent visual conflict between delete/inactive actions and brand red.',
                    ],
                ],
            ],
            [
                'version' => '1.4.0',
                'date' => '2026-10-16',
                'title' => 'Brand Identity, PWA Support, Mobile Card Tables & Live Avatars',
                'type' => 'minor',
                'badge' => 'Major Feature',
                'badge_color' => 'primary',
                'highlights' => 'Crimson Red brand identity, responsive logo system, installable PWA, mobile stacked card view for all tables, and real-time avatar sync.',
                'changes' => [
                    'added' => [
                        'Responsive brand logo component (`brand-logo.blade.php`) showing red \'7\' on mobile and full denim logo on desktop.',
                        'Custom PWA suite with `manifest.json`, Service Worker caching (`sw.js`), and cross-platform installation prompt banner (`pwa-prompt.blade.php`).',
                        'Mobile-first stacked card layout (`stackedOnMobile()`) across all 5 Filament resources eliminating horizontal table scrolling on mobile.',
                        'Custom `EditProfile` page with avatar upload and real-time topbar sync via `avatar-updated` browser event without page reload.',
                        'Compact, expandable `⌘K` global search pill to preserve topbar space.',
                        'Complete brand icon set: favicon, apple-touch-icon, and PWA icon suite.',
                    ],
                ],
            ],
            [
                'version' => '1.3.0',
                'date' => '2026-10-15',
                'title' => 'Shield Enhanced, Poppins Theme, Avatars & Lightbox Gallery',
                'type' => 'minor',
                'badge' => 'Module Update',
                'badge_color' => 'primary',
                'highlights' => 'Fine-grained permissions grid, Poppins corporate theme, user profile avatars, and image gallery lightbox.',
                'changes' => [
                    'added' => [
                        'Installed `agroezinger/filament-shield-enhanced` with grouped permissions checkbox grid.',
                        'Integrated `al-saloul/filament-image-gallery` for clickable image lightbox previews and downloads across tables.',
                        'Custom corporate styling with Poppins typography and slate palette in `theme.css`.',
                        'User avatar management with `HasAvatar` contract and Dashboard `UserAvatarWidget`.',
                        'Enabled native Filament password reset recovery flow.',
                        'Auto-cleanup lifecycle listener to delete replaced or deleted images from storage.',
                    ],
                ],
            ],
            [
                'version' => '1.2.0',
                'date' => '2026-10-14',
                'title' => 'Purchases Tracking, Payments & Slide-Over Modals',
                'type' => 'minor',
                'badge' => 'Module Update',
                'badge_color' => 'primary',
                'highlights' => 'Complete purchase orders tracking, multi-item line purchases, payment recording, and slide-over modals.',
                'changes' => [
                    'added' => [
                        'Purchase resource with purchase date, invoice number, vendor relation, items repeater, and total calculation.',
                        'Payments relation manager with payment modes, references, notes, and balance due calculation.',
                        'Strict slide-over modals (`->slideOver()`) across all resource Create and Edit actions.',
                        'Dashboard and resource statistics widgets for purchases and vendor balances.',
                    ],
                ],
            ],
            [
                'version' => '1.1.0',
                'date' => '2026-10-13',
                'title' => 'Purchase Module & Vendor Management',
                'type' => 'minor',
                'badge' => 'Module Update',
                'badge_color' => 'primary',
                'highlights' => 'Item catalog for materials and comprehensive vendor management with banking and multi-QR details.',
                'changes' => [
                    'added' => [
                        '`PurchaseItem` resource with item code, unit, category, preferred vendor, and image uploads.',
                        'In-form vendor quick creation action (`->createOptionForm()`) on purchase item preferred vendor select.',
                        '`Vendor` resource with multi-contact repeater, PAN/VAT, multi-QR codes (eSewa, Khalti, Bank), and optional bank details.',
                    ],
                ],
            ],
            [
                'version' => '1.0.2',
                'date' => '2026-10-12',
                'title' => 'Shield Role Visibility & Super Admin Gate Bypass',
                'type' => 'patch',
                'badge' => 'Bug Fix',
                'badge_color' => 'warning',
                'highlights' => 'Resolved Shield roles and permissions visibility in admin panel by registering Super Admin Gate bypass.',
                'changes' => [
                    'fixed' => [
                        'Registered Super Admin Gate bypass in `AppServiceProvider` to grant unhindered access to Shield role resources.',
                    ],
                ],
            ],
            [
                'version' => '1.0.1',
                'date' => '2026-10-11',
                'title' => 'Notification Topic Update',
                'type' => 'patch',
                'badge' => 'Maintenance',
                'badge_color' => 'gray',
                'highlights' => 'Configured ntfy.sh notification topic to 7sdkamkaj-tasks with automated curl script.',
                'changes' => [
                    'changed' => [
                        'Updated `.env` and `notify.sh` to use the dedicated topic `7sdkamkaj-tasks`.',
                    ],
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '2026-10-10',
                'title' => 'Initial Release - Filament Panel & User Management',
                'type' => 'major',
                'badge' => 'Initial Release',
                'badge_color' => 'success',
                'highlights' => 'Baseline system setup on Laravel 13, Filament v5, User management, Filament Shield, and automated ntfy push notifications.',
                'changes' => [
                    'added' => [
                        'Filament v5 admin panel initialized on path `/` with ID `admin`.',
                        'User Resource with status toggle, role assignment, and mobile-friendly fields.',
                        'Filament Shield v4.3 integration with Spatie Permissions.',
                        'Autonomous agent operational rules and memory documentation in `AGENTS.md` and `GEMINI.md`.',
                        'Automated push notification script (`scripts/notify.sh`).',
                    ],
                ],
            ],
        ];
    }

    public static function getLatestRelease(): array
    {
        return self::getReleases()[0];
    }
}
