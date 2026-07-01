# No Hardware — UI Guardrails

## Do not implement (v1)

| Feature | Reason |
|---------|--------|
| `keydown` scanner wedge handlers | No scanner hardware |
| ESC/POS JS libraries | No thermal printer |
| Fixed 58mm/80mm receipt width | Browser print only |
| WebUSB / WebSerial barcode | No hardware |
| Cash drawer `window.open` pulse | Manual cash count only |
| npm: `node-thermal-printer`, `escpos`, etc. | Out of scope |

## Do implement

- Debounced Livewire search (name, sku, generic_name, barcode column).
- `@focus` search on mount (`wire:init` or Alpine `x-init`).
- Receipt "Print" button → `@click="window.print()"`.
- `@media print { ... }` hiding nav/sidebar.

## Barcode field UX

- Product form: optional barcode text input (manual entry).
- POS: if user types numeric string matching stored barcode, treat as product match — same as search, not hardware.

## If user asks for hardware

Respond: deferred to post-v1 per `PHARMACY_POS_PLAN.md`. Do not implement unless user explicitly overrides v1 scope.
