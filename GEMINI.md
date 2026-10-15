# GEMINI.md - Agent Instructions & Project Memory

## Core Operational Rules (MANDATORY)

Every AI assistant or agent working on this codebase MUST strictly adhere to the following rules at all times:

1. **AUTONOMOUS EXECUTION & NO CONFIRMATION GATES TO PROCEED**:
   - Always proceed autonomously with the requested implementation without stopping to ask for permission to proceed.
   - Execute tasks end-to-end adhering strictly to user requirements and established memory.

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
   - Always use slide-over modals (`->slideOver()`) on Header `CreateAction` and Table `EditAction` and the modal should cover 70 to 80 percent of the screen in desktops.

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
- **Role & Permissions**: `bezhansalleh/filament-shield` v4.3 with `agroezinger/filament-shield-enhanced` v0.3.0 & `spatie/laravel-permission`
  - Resource: `App\Filament\Resources\Roles\RoleResource`
  - Features: Fine-grained grid permissions checkboxes layout, grouped by navigation tabs, slide-over create/edit
- **UI & Theme**:
  - Primary Brand Color: Vibrant Red (`Color::Red` matching Seven Series Authentic Denim brand identity)
  - Danger Accent: Orange (`Color::Orange`) to distinguish danger/delete/overdue states from the brand red
  - Brand Assets: Responsive `brand-logo.blade.php` (red '7' symbol on mobile, full "7 SEVEN SERIES Authentic Denim" logo on desktop), `favicon.png`, `apple-touch-icon.png`
  - Custom theme: `resources/css/filament/admin/theme.css` with Poppins font and modern corporate styling
  - Mobile Card Listing: Native app card layout on mobile via `->stackedOnMobile()` on all resources, eliminating horizontal scroll
  - Compact Global Search: Space-efficient search pill with `⌘K` badge expanding on focus to maximize topbar utility
  - Live auto-reload: Vite watching Filament schemas, views, and models
  - Gallery preview: `al-saloul/filament-image-gallery` for interactive image lightbox
  - Avatar management: `HasAvatar` on User model, custom `EditProfile` page with avatar upload, `UserAvatarWidget` on Dashboard, and real-time topbar avatar listener (`avatar-updated` event) without page reloads
  - PWA Support: Installable PWA with `manifest.json`, Service Worker caching (`sw.js`), and install prompt banner for Android/iOS/Desktop
  - In-App Changelog: Interactive release notes timeline at `/changelog` (`App\Filament\Pages\Changelog`), centralized `ChangelogService`, clickable sidebar version pill, and Keep a Changelog `CHANGELOG.md`
  - Password recovery: Enabled via native Filament `passwordReset()`
  - File lifecycle: Auto-deletion of removed, replaced, or deleted images from storage disk across all models
- **Current Version**: `1.5.0`
