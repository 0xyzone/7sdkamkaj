# AGENTS.md - Agent Instructions & Project Memory

## Core Operational Rules (MANDATORY)

Every AI assistant or agent working on this codebase MUST strictly adhere to the following rules at all times:

1. **NO ASSUMPTIONS & ASK BEFORE EXECUTION**:
   - DO NOT make any assumptions about architecture, requirements, configurations, or business logic.
   - Always ask the user for confirmation and input whenever clarification or critical decisions are needed before executing any code.

2. **GIT COMMIT & PUSH TO MAIN ON TASK COMPLETION**:
   - Every time a task or feature is completed, stage the changes, create a clear and descriptive commit message, and push directly to `origin/main`.

3. **SEMANTIC VERSIONING & TAGGING**:
   - Every time a new feature or update is added, determine the nature of the change and update the Git version tag accordingly:
     - **Major Update (`X.0.0`)**: Breaking changes or fundamental architectural shifts.
     - **Minor Update (`1.X.0`)**: New feature additions or significant module implementations.
     - **Patch / Partial Update (`1.0.X`)**: Bug fixes, minor UI tweaks, or maintenance tasks.
   - If no prior version tag exists, the baseline version is **`1.0.0`**.
   - Always create annotated or lightweight tags (e.g. `git tag -a v1.0.0 -m "Release v1.0.0"`) and push tags to remote (`git push origin --tags`).

4. **MEMORY CHECK TO MINIMIZE TOKENS & ANALYSIS**:
   - Always check and maintain this memory documentation first before performing repetitive file searches or deep codebase scans, keeping token usage efficient.

5. **SLIDE-OVER MODALS (NO FULL CREATE/EDIT PAGES)**:
   - Do NOT create separate create or edit pages for any resource.
   - Always use slide-over modals (`->slideOver()`) on Header `CreateAction` and Table `EditAction`.

6. **MOBILE-FIRST UI & LAYOUT**:
   - The user primarily manages this app on mobile devices.
   - Design table columns and form layouts with mobile responsiveness in mind:
     - Use primary columns with secondary details in `description()`.
     - Avoid wide, horizontal-scrolling tables by using badges, icons, and collapsible/accordion elements.
     - Form grids should be single-column on mobile (`['default' => 1, 'sm' => 2]`).

7. **TASK COMPLETION PUSH NOTIFICATION (ntfy.sh)**:
   - When a task is finalized, immediately send a push notification to the configured ntfy topic:
     - Topic: `7sdkamkaj-tasks` (configurable via `NTFY_TOPIC` in `.env`)
     - Command: `./scripts/notify.sh "<Task Summary>" "<Task Title>"` or `php artisan notify:task "<Message>" --title="<Title>"`
     - URL to subscribe: `https://ntfy.sh/7sdkamkaj-tasks`

---

## Project Overview & Architecture

- **Project Name**: 7 Series Kamkaj
- **Framework**: Laravel 13 (PHP 8.3+)
- **Admin Panel**: Filament v5
  - Panel ID: `admin`
  - Path: `/`
  - Auth: Email & Password
  - Super Admin: `admin@admin.com`
- **Role & Permissions**: `bezhansalleh/filament-shield` v4.3 with `spatie/laravel-permission`
  - Gate interception enabled (`define_via_gate => true`)
- **Modules**:
  - **User Management** (`User Management` group):
    - Model: `App\Models\User`
    - Resource: `App\Filament\Resources\Users\UserResource`
    - Status: `is_active` boolean
  - **Purchase Module** (`Purchase` group):
    - **Purchases**:
      - Model: `App\Models\Purchase`
      - Resource: `App\Filament\Resources\Purchases\PurchaseResource`
      - Features: PO number, vendor selection, line items with auto-computed subtotals, payments tracking, bill & product images, quick 'Record Payment' slide-over action
    - **Purchase Items**:
      - Model: `App\Models\PurchaseItem`
      - Resource: `App\Filament\Resources\PurchaseItems\PurchaseItemResource`
      - Fields: name, category, UOM, cost_price, vendor_id (with quick inline creation), description, images (multiple)
    - **Vendors**:
      - Model: `App\Models\Vendor`
      - Resource: `App\Filament\Resources\Vendors\VendorResource`
      - Fields: name, email, address, pan_vat_number, website, contacts (optional), qrs (optional), bank_accounts (optional), notes, is_active
- **Current Version**: `1.2.0`
