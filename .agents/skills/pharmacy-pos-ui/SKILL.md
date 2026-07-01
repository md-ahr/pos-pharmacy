---
name: pharmacy-pos-ui
description: "Builds Livewire POS and admin screens using Tyro dashboard for the Pharmacy POS app. Use when creating Livewire components, Blade views, POS product search, cart, payments, receipts, inventory forms, dashboard widgets, or keyboard UX. Enforces software-only v1 (no scanner/printer hardware), Tyro layout reuse, and thin Livewire components delegating to Services."
---

# Pharmacy POS UI

## Layout & components

- Extend Tyro dashboard layout for all authenticated screens.
- Reuse Tyro Blade components (cards, tables, forms, modals).
- Do not introduce a second admin UI kit or standalone Tailwind dashboard.

## File structure

| Type | Path |
|------|------|
| Livewire class | `app/Livewire/{Domain}/{Name}.php` |
| Livewire view | `resources/views/livewire/{domain}/{name}.blade.php` |
| Receipt | `resources/views/sales/receipt.blade.php` |
| Print CSS | `@media print` in receipt view or dedicated print stylesheet |

## Livewire rules

- State + event handlers only — call Services/Actions for checkout, stock, invoices.
- Use Form Requests or `$rules` only for simple forms; prefer Form Request classes for writes.
- Livewire tests: activate `pest-testing` skill.

## POS screen (Phase 6)

### Product search (software-only)

- Autofocus search input on load.
- Search: name, SKU, generic name; optional exact barcode match if typed/pasted.
- Keyboard: arrow keys highlight results, Enter adds to cart.
- **No** scanner wedge listeners, WebUSB, or dedicated barcode input modes.

### Cart

- Line qty, unit selector (product_units), remove line.
- FEFO batch display with manual override dropdown.
- Prescription flags per line + sale-level prescriber fields.

### Payment

- Cash / card / mobile; split payments supported.
- Hold/resume (parked sales).

### Receipt

- Print-friendly Blade + browser `window.print()`.
- A4/letter `@media print` — **no** 58mm/80mm thermal layouts.
- Optional "Download PDF" via dompdf.

## Inventory screens

- Product CRUD with categories, manufacturers, product_units.
- Batch intake, stock adjustments, low-stock and near-expiry widgets.

See [rules/no-hardware.md](rules/no-hardware.md) for forbidden integrations.
