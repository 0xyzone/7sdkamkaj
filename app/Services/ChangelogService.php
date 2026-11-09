<?php

namespace App\Services;

class ChangelogService
{
    public const CURRENT_VERSION = '1.15.1';

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
                'version' => '1.15.1',
                'date' => '2026-11-09',
                'title' => 'Quick Print Slide-Over Canvas Engine & Button Text Binding Fix',
                'type' => 'patch',
                'badge' => 'Fix',
                'badge_color' => 'success',
                'highlights' => 'Resolved blank preview canvas and missing button label text in the Resource List Quick Print slide-over modal. Pre-loaded canvas engines, implemented robust dependency polling, supported both Studio JSON and legacy element arrays, and eliminated HTML single-quote attribute collisions.',
                'changes' => [
                    'fixed' => [
                        'Fixed blank thermal preview canvas: now properly loads Studio JSON and legacy element arrays into Fabric StaticCanvas with enableRetinaScaling disabled.',
                        'Fixed missing button label text: replaced fragile x-text quote evaluations with solid x-show state switches for Direct USB Print, Bluetooth Print, and Server Spool.',
                        'Pre-loaded core script assets (fabric.js, JsBarcode, qrcode) at the modal root with active dependency readiness polling before preview rendering.',
                        'Added image layer loading synchronization to ensure embedded photos and logos render completely before 1-bit thermal conversion.',
                    ],
                ],
            ],
            [
                'version' => '1.15.0',
                'date' => '2026-11-09',
                'title' => 'Mobile 2-Line Studio UI Overhaul & iOS/Android 1-Tap Wireless Printing',
                'type' => 'minor',
                'badge' => 'Feature',
                'badge_color' => 'primary',
                'highlights' => 'Complete mobile responsiveness optimization for Label Studio featuring a clean 2-line top navigation header and a 2-line bottom toolbar dock ensuring all tools and menus are 100% visible and accessible without horizontal scroll. Added 1-tap "Share to Deli App" via native Web Share API and AirPrint/System Spooler support to enable effortless wireless printing from Brave on iOS and Android.',
                'changes' => [
                    'added' => [
                        '2-line mobile top menubar: Row 1 displays Brand, full-width template dropdown selector and Panel link; Row 2 houses Undo/Redo, Zoom (-/Fit/+), Layers drawer button with live badge, Save with dirty dot, and prominent Print button.',
                        '2-line mobile bottom toolbar dock: Row 1 exposes all creation & insertion tools (Select, Text, Barcode, QR, Image, Rectangle, Ellipse, Line); Row 2 exposes drawing, navigation, and utilities (Pen, Eraser, Hand, Zoom, Ink Swatch, Layers drawer, and 1-tap Delete).',
                        '1-tap Share to Deli App: instantly exports 1-bit thermal label PNG via navigator.share file sheet directly to Deli Label or RawBT on iOS and Android.',
                        'AirPrint / System Print: zero-margin thermal sticker print preview triggered directly via native browser print spooler.',
                        'Mobile OS-aware Bluetooth guide: dedicated guidance for iOS (Apple WebKit policy & 1-tap alternatives) and Android (Brave flag toggle & Chrome link).',
                        'Filament resource page quick-print modal: updated with 1-tap Share to Deli App and AirPrint buttons.',
                    ],
                ],
            ],
            [
                'version' => '1.14.0',
                'date' => '2026-11-09',
                'title' => 'Mobile Screen Optimization, In-Page Quick Print Modal & Deli Bluetooth Printing',
                'type' => 'minor',
                'badge' => 'Feature',
                'badge_color' => 'primary',
                'highlights' => 'Complete mobile workstation overhaul for Label Studio featuring a tabbed drawer for instant Layers and Properties access, upfront creation tools (Text, Barcode, QR, Image) with 1-tap placement, zero-overlap mobile topbar, in-page slide-over print dialog on the Label Templates resource page with real-time preview, and enhanced Deli E886BW Bluetooth printing support with transparent BLE UART discovery and Bluetooth Serial SPP fallback.',
                'changes' => [
                    'added' => [
                        'Mobile tabbed drawer: dedicated Layers tab with live layer count badge, visibility toggles, lock, reorder, duplicate and delete.',
                        'Front-loaded mobile dock: Move, Text, Barcode, QR, Image, Shapes, Pen, and Layers buttons immediately accessible without scrolling.',
                        'One-tap mobile creation: tapping Barcode, QR, Image, or Text directly places elements at canvas center on mobile screens.',
                        'Resource page in-page print modal: clicking Print in /label-templates opens a slide-over modal with live 1-bit WYSIWYG preview, dynamic variables form, and direct WebUSB, Bluetooth, and server spool printing without navigating away.',
                        'Enhanced Deli Bluetooth printing: added standard Deli BLE service UUIDs (0000ffe0, 0000ff00, 49535343, 6e400001, 0000fee7) with acceptAllDevices discovery and Web Serial SPP fallback.',
                    ],
                ],
            ],
            [
                'version' => '1.13.0',
                'date' => '2026-11-09',
                'title' => 'Print Dialog Dynamic Variables Form with Real-Time Preview',
                'type' => 'minor',
                'badge' => 'Feature',
                'badge_color' => 'primary',
                'highlights' => 'The Print Dialog now dynamically inspects the active design and presents input fields for only the variables actually used (e.g. {{item_name}}, {{size}}, {{price}}, {{color}}, {{barcode}}). Typing in any field immediately updates the thermal 1-bit print preview in real-time before spooling.',
                'changes' => [
                    'added' => [
                        'Design-specific variable scanner: only variables actually placed on the design are presented in the print dialog.',
                        'Real-time live print preview: instant re-rendering of text, barcodes, and QR codes as soon as variable values are typed.',
                        'Seamless sync: updated values persist across print jobs and sync with the Label Studio variables inspector.',
                    ],
                ],
            ],
            [
                'version' => '1.12.0',
                'date' => '2026-11-09',
                'title' => 'Interactive Rounded Corner Dragging & Radius Controls',
                'type' => 'minor',
                'badge' => 'Feature',
                'badge_color' => 'primary',
                'highlights' => 'Rectangles now feature interactive on-canvas corner radius handles inside all four corners (similar to Figma and Illustrator) allowing designers to drag inwards/outwards to round or sharpen corners directly on the canvas, paired with live numeric and slider controls in the Properties panel.',
                'changes' => [
                    'added' => [
                        'Interactive on-canvas corner radius handles: drag circular red handles inside the rectangle corners to round corners dynamically.',
                        'Real-time Properties synchronization: Corner Roundness slider and Corner radius (mm) number input update smoothly while dragging.',
                        'Live input listeners: typing or stepping numeric fields in the Properties panel updates the canvas instantly without requiring blur/enter.',
                    ],
                ],
            ],
            [
                'version' => '1.11.0',
                'date' => '2026-11-09',
                'title' => 'Native Vector SVG Importing & Path Line Editing',
                'type' => 'minor',
                'badge' => 'Major Feature',
                'badge_color' => 'primary',
                'highlights' => 'SVG files are now parsed as native vector paths rather than pixelated raster bitmaps. Vector graphics retain razor-sharp edges at all scales and zoom levels, automatically normalize to thermal black ink, and can be ungrouped into individual editable path lines that work with pen and vector styling tools.',
                'changes' => [
                    'added' => [
                        'Native SVG vector parser: SVGs uploaded or dropped onto Label Studio are parsed into true vector paths with infinite sharpness.',
                        'Automatic thermal ink normalization: colored SVG strokes and fills convert to clean black thermal ink (#000000).',
                        'Vector Group & Path Line Inspector: control fill, stroke, line thickness (in mm), and dashed styling directly in the Properties panel.',
                        'Ungroup into Paths (⇧⌘G): separate any vector group into individual vector curves and shapes to reposition, edit, or delete them.',
                        'Group Paths (⌘G): combine multiple selected vector paths into a single unified vector group.',
                    ],
                ],
            ],
            [
                'version' => '1.10.0',
                'date' => '2026-11-09',
                'title' => 'Automatic 1-Bit Black & White Thermal Image Conversion',
                'type' => 'minor',
                'badge' => 'Feature',
                'badge_color' => 'primary',
                'highlights' => 'When adding images or brand logos into Label Studio, they are now automatically converted into 1-bit monochrome black and white on the live canvas. Designers see pixel-perfect feedback matching the physical thermal printer output, with live threshold/darkness tuning, photographic dithering, negative inverting, and white-background transparency removal.',
                'changes' => [
                    'added' => [
                        'Automatic thermal black & white conversion on image upload, drag-and-drop, and brand asset selection.',
                        'Preserves PNG transparency so logos integrate smoothly without white bounding boxes.',
                        'Live Thermal B&W controls in Properties panel: Darkness / Threshold slider, Dither mode (for photos), Invert (negative), and Make White Transparent.',
                        'Direct drag-and-drop support: drop image files directly onto the canvas to insert as thermal black & white.',
                        'Automatic backward-compatibility conversion of existing color images upon loading templates.',
                    ],
                ],
            ],
            [
                'version' => '1.9.9',
                'date' => '2026-11-09',
                'title' => 'Web Bluetooth Quick Activation Dialog for Brave Browser',
                'type' => 'patch',
                'badge' => 'Enhancement',
                'badge_color' => 'info',
                'highlights' => 'Added a dedicated helper modal dialog in Label Studio when clicking Bluetooth Print in Brave browser. Provides step-by-step instructions and a 1-click URL copy button for brave://flags/#enable-web-bluetooth to enable seamless wireless thermal label printing.',
                'changes' => [
                    'added' => [
                        'Interactive helper popup dialog `#bluetoothHelpDialog` triggered on Bluetooth Print in Brave.',
                        '1-click "Copy URL" button and auto-select for `brave://flags/#enable-web-bluetooth`.',
                        'Quick tip highlighting instant Direct USB Print alternative.',
                    ],
                ],
            ],
            [
                'version' => '1.9.1',
                'date' => '2026-11-09',
                'title' => 'Standalone Photoshop-Style Label Studio & WYSIWYG Thermal Printing',
                'type' => 'minor',
                'badge' => 'Major Feature',
                'badge_color' => 'primary',
                'highlights' => 'Re-architected Thermal Label Studio as an independent, full-screen Photoshop-inspired application outside Filament. Includes left toolbar with tools (Move, Text, Pen, Eraser, Barcodes, QR, Shapes, Logo/Image Upload), central live 203 DPI canvas with rulers and zoom, right layers & properties inspector, and 1:1 pixel-perfect WYSIWYG thermal bitmap generation for Deli E886BW.',
                'changes' => [
                    'added' => [
                        'Standalone route `/label-studio` outside Filament for distraction-free, full-featured label designing.',
                        'Photoshop-style dark-mode desktop UI with left tool panel, top options bar, center scrollable canvas stage with zoom/fit, and right layers & inspector panel.',
                        'Image & Logo Upload tool: upload any custom garment brand logo, wash care symbol, or graphic directly onto the label.',
                        'Pixel-perfect WYSIWYG raster rendering: exports HTML5 canvas directly to 1-bit monochrome TSPL BITMAP data stream, ensuring the printed thermal sticker identically matches the screen design.',
                        'Integrated layer management: re-order, lock, hide, or delete individual canvas layers.',
                        'Direct Deli E886BW spooling with live printer status checking and batch copies.',
                    ],
                    'fixed' => [
                        'Fixed non-dehydrated state for `standard_preset` virtual field.',
                        'Escaped Blade template curly braces in label design placeholders.',
                    ],
                ],
            ],
            [
                'version' => '1.8.0',
                'date' => '2026-11-09',
                'title' => 'Thermal Printer Management & Garment Label Design Module',
                'type' => 'minor',
                'badge' => 'New Module',
                'badge_color' => 'primary',
                'highlights' => 'Introduced Printing & Labels category with Printer hardware management and Label Templates for garment labeling with Deli E886BW thermal printer.',
                'changes' => [
                    'added' => [
                        'Printer hardware resource supporting macOS CUPS auto-detection and raw TCP/IP network printers.',
                        'Live printer status monitoring (Idle/Ready, Busy, Offline).',
                        'Label Templates resource with standard presets (50x30mm, 50x70mm, 40x30mm, 60x40mm, 80x50mm, 100x150mm) and custom nozzle width validation.',
                        'Direct TSPL command generation for barcodes, text, and geometric elements.',
                    ],
                ],
            ],
            [
                'version' => '1.7.0',
                'date' => '2026-10-17',
                'title' => 'Sales Management Module & Inactive Account Protection',
                'type' => 'minor',
                'badge' => 'New Module',
                'badge_color' => 'primary',
                'highlights' => 'Introduced the Sales module with Distributors and Sales Records, dual bill/goods photo attachments, payment ledger tracking, quick payment logs, sales analytics, and custom redirection for deactivated accounts.',
                'changes' => [
                    'added' => [
                        'New `Sales` navigation category featuring `DistributorResource` and `SaleRecordResource`.',
                        '`DistributorResource` managing corporate details (PAN/VAT, address, website, notes), attached contact persons with multiple phone types (Mobile, WhatsApp, Office), and optional banking details.',
                        '`SaleRecordResource` with auto-generated reference numbers (`SALE-YYYYMM-XXXX`), bill number tracking, inline distributor creation from dropdown, and fulfillment status.',
                        'Separate attachments for sales bills/invoices, finished goods photos, and payment receipt vouchers with automatic file cleanup on deletion/replacement.',
                        'Multi-instalment payment repeater tracking receipt dates, payment modes (Cash, Bank Transfer, Fonepay QR, eSewa, Khalti, Cheque), and vouchers.',
                        'Auto-calculation of paid amounts, outstanding due receivables, and payment status (`unpaid`, `partial`, `paid`).',
                        'Quick "Log Payment" slide-over table action for instant settlement logging from the table view.',
                        '`SaleStatsOverview` widget showing total billed revenue, collected cash, outstanding receivables, and active distributor counts.',
                        'Deactivated account protection: inactive users (`is_active = false`) are automatically redirected to `/account-deactivated` displaying a clean notice without contact details and a secure Log Out action.',
                        'Filament Shield permissions and policies generated for `DistributorResource` and `SaleRecordResource`.',
                    ],
                ],
            ],
            [
                'version' => '1.6.1',
                'date' => '2026-10-17',
                'title' => 'Filament Shield Role Actions Compatibility Fix',
                'type' => 'patch',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'highlights' => 'Resolved BadMethodCallException when rendering and executing Shield Role create and edit slide-over actions, ensuring smooth permission synchronization and modal responsive sizing.',
                'changes' => [
                    'fixed' => [
                        'Fixed unhandled `BadMethodCallException: Method Filament\Actions\CreateAction::mutateFormDataBeforeCreate does not exist` on `/shield/roles`.',
                        'Replaced invalid page lifecycle hook calls (`mutateFormDataBeforeCreate` and `mutateFormDataBeforeSave`) on `CreateAction` and `EditAction` with proper action `using()` and `after()` handlers.',
                        'Preserved all form permissions inputs during role creation and update while ensuring only valid role columns are persisted to the roles table.',
                        'Configured slide-over modal width (`7xl`) for desktop layout to cover 70-80% of the screen.',
                        'Added comprehensive feature tests covering Shield roles page access and Livewire component rendering.',
                    ],
                ],
            ],
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
