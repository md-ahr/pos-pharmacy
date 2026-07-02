<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->invoice_no }}</title>
    <style>
        :root {
            color-scheme: light;
            --font: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --surface: #ffffff;
            --canvas: #f1f5f9;
            --accent: #0d9488;
            --accent-soft: #ccfbf1;
            --accent-ink: #115e59;
            --shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 2rem 1rem 3rem;
            color: var(--ink);
            background: var(--canvas);
            font-family: var(--font);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .page {
            max-width: 760px;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.625rem;
            margin-bottom: 1.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--ink);
            border-radius: 0.625rem;
            padding: 0.55rem 0.95rem;
            font: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: #0f766e;
            border-color: #0f766e;
        }

        .btn-ghost {
            background: transparent;
        }

        .btn svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        .receipt {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .receipt-accent {
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #14b8a6);
        }

        .receipt-body {
            padding: 2rem 2rem 1.75rem;
        }

        .receipt-header {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .pharmacy-name {
            margin: 0 0 0.35rem;
            font-size: 1.625rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
        }

        .receipt-tagline {
            margin: 0 0 0.75rem;
            color: var(--muted);
            font-size: 0.9375rem;
        }

        .branch-line {
            margin: 0;
            color: var(--muted);
            font-size: 0.875rem;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            min-width: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .meta-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--ink);
            word-break: break-word;
        }

        .meta-value.mono {
            font-variant-numeric: tabular-nums;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .callout {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.875rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.75rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 0.875rem;
        }

        .callout strong {
            display: block;
            margin-bottom: 0.15rem;
            color: #78350f;
        }

        .section-title {
            margin: 0 0 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .items th {
            padding: 0.625rem 0.5rem;
            border-bottom: 2px solid var(--border);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            text-align: left;
        }

        .items th.num,
        .items td.num {
            text-align: right;
            white-space: nowrap;
        }

        .items td {
            padding: 0.875rem 0.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 0.9375rem;
        }

        .items tbody tr:last-child td {
            border-bottom: none;
        }

        .item-name {
            font-weight: 600;
            color: var(--ink);
        }

        .item-meta {
            margin-top: 0.2rem;
            font-size: 0.8125rem;
            color: var(--muted);
        }

        .badge {
            display: inline-block;
            margin-top: 0.35rem;
            padding: 0.125rem 0.45rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-ink);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }

        .summary {
            width: min(100%, 320px);
            padding: 1rem 1.125rem;
            border-radius: 0.875rem;
            background: #f8fafc;
            border: 1px solid var(--border);
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.3rem 0;
            font-size: 0.9375rem;
            color: var(--muted);
        }

        .summary-line span:last-child {
            color: var(--ink);
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }

        .summary-line.total {
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border);
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .summary-line.total span:last-child {
            font-size: 1.125rem;
            color: var(--accent-ink);
        }

        .summary-line.emphasis span:last-child {
            font-weight: 700;
        }

        .payments {
            margin-bottom: 1.5rem;
        }

        .payment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .payment-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid var(--border);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ink);
        }

        .payment-chip .method {
            color: var(--muted);
            font-weight: 500;
        }

        .receipt-footer {
            padding-top: 1.25rem;
            border-top: 1px dashed var(--border);
            text-align: center;
            color: var(--muted);
            font-size: 0.9375rem;
        }

        .receipt-footer p {
            margin: 0;
        }

        @media (max-width: 560px) {
            body {
                padding: 1rem 0.75rem 2rem;
            }

            .receipt-body {
                padding: 1.25rem 1rem 1rem;
            }

            .meta-grid {
                grid-template-columns: 1fr;
            }

            .items th:nth-child(2),
            .items td:nth-child(2) {
                display: none;
            }
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .receipt {
                border: none;
                border-radius: 0;
                box-shadow: none;
            }

            .receipt-accent {
                display: none;
            }

            .summary {
                background: transparent;
                border-color: #e2e8f0;
            }

            .payment-chip {
                background: transparent;
            }
        }
    </style>
</head>
<body>
    @php
        $currency = $settings->currency_code ?? 'USD';
        $money = static fn (float|string $amount): string => \Illuminate\Support\Number::currency((float) $amount, $currency);
    @endphp

    <div class="page">
        @unless($forPdf ?? false)
            <div class="toolbar">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 9V2h12v7"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <path d="M6 14h12v8H6z"/>
                    </svg>
                    Print receipt
                </button>
                <a href="{{ route('pharmacy.pos.receipt.pdf', $sale) }}" class="btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download PDF
                </a>
                <button type="button" class="btn btn-ghost" onclick="window.close()">Close</button>
            </div>
        @endunless

        <article class="receipt">
            <div class="receipt-accent" aria-hidden="true"></div>

            <div class="receipt-body">
                <header class="receipt-header">
                    <h1 class="pharmacy-name">{{ $sale->tenant->name }}</h1>
                    @if(! empty($settings->receipt_header))
                        <p class="receipt-tagline">{!! nl2br(e($settings->receipt_header)) !!}</p>
                    @endif
                    <p class="branch-line">{{ $sale->branch->name }}</p>
                    @if($sale->branch->address)
                        <p class="branch-line">{{ $sale->branch->address }}</p>
                    @endif
                </header>

                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Invoice</span>
                        <span class="meta-value mono">{{ $sale->invoice_no }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date</span>
                        <span class="meta-value">@displayDatetime($sale->sold_at)</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Cashier</span>
                        <span class="meta-value">{{ $sale->cashier->name }}</span>
                    </div>
                    @if($sale->customer)
                        <div class="meta-item">
                            <span class="meta-label">Customer</span>
                            <span class="meta-value">{{ $sale->customer->name }}</span>
                        </div>
                    @endif
                </div>

                @if($sale->prescription_required)
                    <div class="callout">
                        <div>
                            <strong>Prescription sale</strong>
                            @if($sale->prescriber_name)
                                <div>
                                    Prescriber: {{ $sale->prescriber_name }}
                                    @if($sale->prescriber_reg_no)
                                        ({{ $sale->prescriber_reg_no }})
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <h2 class="section-title">Items</h2>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Batch / Expiry</th>
                            <th class="num">Qty</th>
                            <th class="num">Price</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>
                                    <div class="item-name">{{ $item->product->name }}</div>
                                    @if($item->is_prescription_item)
                                        <span class="badge">Rx item</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="item-meta">{{ $item->batch->batch_no }}</div>
                                    <div class="item-meta">Exp. {{ $item->batch->expiry_date->format('M j, Y') }}</div>
                                </td>
                                <td class="num">{{ $item->quantity }}</td>
                                <td class="num">{{ $money($item->unit_price) }}</td>
                                <td class="num">{{ $money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary-row">
                    <div class="summary">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span>{{ $money($sale->subtotal) }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Discount</span>
                            <span>{{ $money($sale->discount_amount) }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Tax</span>
                            <span>{{ $money($sale->tax_amount) }}</span>
                        </div>
                        <div class="summary-line total">
                            <span>Total</span>
                            <span>{{ $money($sale->total) }}</span>
                        </div>
                        <div class="summary-line emphasis">
                            <span>Paid</span>
                            <span>{{ $money($sale->paid_amount) }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Change</span>
                            <span>{{ $money($sale->change_amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="payments">
                    <h2 class="section-title">Payments</h2>
                    <ul class="payment-list">
                        @foreach($sale->payments as $payment)
                            <li class="payment-chip">
                                <span class="method">{{ ucfirst($payment->method->value) }}</span>
                                <span>{{ $money($payment->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <footer class="receipt-footer">
                    @if(! empty($settings->receipt_footer))
                        <p>{!! nl2br(e($settings->receipt_footer)) !!}</p>
                    @else
                        <p>Thank you for your purchase.</p>
                    @endif
                </footer>
            </div>
        </article>
    </div>
</body>
</html>
