# Synapse — AI-First Business Management Platform

Synapse is a next-generation, high-fidelity business management dashboard built with Laravel, Alpine.js, and TailwindCSS. It integrates an intelligent AI assistant, a robust double-entry-style accounting ledger with progressive warnings, and a multi-channel media distribution publisher—all wrapped in a premium space-obsidian design system.

---

## 🌌 The Design System (NexusAI Language)

Synapse implements a professional, WCAG AA contrast-compliant dark theme as its default layout, supporting seamless run-time transitions to a clean light mode:
- **Deep Space Palette**: Primary backdrops (`#08080f` / `#0d0d1a`), card panels (`#12121f` / `#16162a`), and bright accents featuring violet-to-blue linear gradients.
- **Responsive Auroras**: Floating radial gradient backdrops (`.aur`) to give depth and modern style.
- **Modern Typography**: Styled globally using the **Space Grotesk** font face from Google Fonts.
- **Contrast Ratios**: All text elements (including secondary labels and badges) have been aligned to exceed the WCAG AA **4.5:1** contrast ratio guidelines.

---

## ⚡ Core Modules & Features

### 🤖 1. AI Assistant & Task Management
- **Interactive AI Chat**: Custom chat layout featuring message bubbles, interactive history clear commands, and animated typing indicators.
- **Responsive Kanban Task Board**:
  - Drag-and-drop task status updates (Pending, In Progress, Completed, Cancelled).
  - Preserved Toggle view options (swap between standard List and Kanban boards).
  - Immediate AJAX status changes persisted in the database.
- **Real-Time Upcoming Task Alerts**:
  - Global background polling client (runs every 30 seconds) querying upcoming deadlines.
  - Custom warning modals popping up when a task is due in **1 hour**, **30 minutes**, or **5 minutes**.
  - Interactive modal actions:
    - **Reschedule**: Select a custom date-time picker.
    - **Auto-Reschedule**: Dynamically calculates the next available conflict-free hour slot.
    - **Cancel Task**: Direct status update.
  - Cache-based alert tracking to prevent warning duplicates across pages.

### 💼 2. Accounting & Financial Analytics
- **Ledger & Transactions**: Interactive record sheet mapping daily cash flows with dynamic filters.
- **Live Exchange Rate Auto-Fetching**: Integrated client-side API helper that fetches live **GHS (Ghanaian Cedi)** exchange rates on currency selection, auto-populating fields instantly.
- **Progressive Budget Warnings**:
  - Monitors monthly category spending and generates persistent database notifications at **80%** and **90%** consumption thresholds, plus **100%+** limit breaches.
  - Implements category safety checks to avoid duplicate notification alerts.
- **Interactive Financial Reports**: Computes P&L statements, Balance Sheets, and Trial Balance summaries with responsive tab controls.
- **Data Charts**: Rendered using ApexCharts, fully theme-aware (re-renders styling options dynamically on theme switch).

### 📢 3. Distribution & Media Library
- **Media Library**: Asset library with type classifications, search operations, and safe deletions.
- **Publish Queue**: Scheduling pipeline simulating background queue workers and sending success/error logs to the notification feed.

---

## 🚨 Global Alert Interceptors (SweetAlert2)
- Overrides the browser's default `window.alert` dialog boxes with custom-styled **SweetAlert2** modals.
- Captures `[data-confirm]` submits and link clicks in the **capturing phase** (`useCapture = true`). This instantly halts browser navigation/submits, displays a matching SweetAlert dialog, and safely dispatches `form.requestSubmit()` or routes the user only upon confirmation.
- Keyword analysis automatically assigns modal icons (e.g. "failed" maps to an error mark, "success" maps to a green check).

---

## 🛠️ Local Installation & Setup

1. **Clone and Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Migration & Seeding**:
   Create a local database, update your `.env` configuration, and run:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

4. **Asset Compilation**:
   ```bash
   npm run build
   ```

5. **Start Dev Server**:
   ```bash
   php artisan serve
   ```

---

## 🔑 Default Credentials

- **Admin Account**:
  - **Email**: `admin@synapse.local`
  - **Password**: `password`
