<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->invoice_no }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }

        body {
            margin: 0;
            padding: 2rem;
            color: #111827;
            background: #fff;
        }

        .receipt {
            max-width: 720px;
            margin: 0 auto;
        }

        .receipt-header,
        .receipt-footer {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .meta,
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }

        .items th,
        .items td,
        .meta td {
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 0.25rem;
            text-align: left;
            vertical-align: top;
        }

        .items th {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
        }

        .totals td {
            border: none;
            padding: 0.35rem 0;
        }

        .totals td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        button {
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }

        @media print {
            body {
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        @unless($forPdf ?? false)
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print receipt</button>
            <a href="{{ route('pharmacy.pos.receipt.pdf', $sale) }}">
                <button type="button">Download PDF</button>
            </a>
            <button type="button" onclick="window.close()">Close</button>
        </div>
        @endunless

        <div class="receipt-header">
            <h1>{{ $sale->tenant->name }}</h1>
            @if(! empty($settings->receipt_header))
                <p>{!! nl2br(e($settings->receipt_header)) !!}</p>
            @endif
            <p>{{ $sale->branch->name }}</p>
            @if($sale->branch->address)
                <p>{{ $sale->branch->address }}</p>
            @endif
            <p><strong>Invoice:</strong> {{ $sale->invoice_no }}</p>
            <p><strong>Date:</strong> @displayDatetime($sale->sold_at)</p>
            <p><strong>Cashier:</strong> {{ $sale->cashier->name }}</p>
        </div>

        @if($sale->prescription_required)
            <p><strong>Prescription sale</strong></p>
            @if($sale->prescriber_name)
                <p>Prescriber: {{ $sale->prescriber_name }} @if($sale->prescriber_reg_no)({{ $sale->prescriber_reg_no }})@endif</p>
            @endif
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Batch / Expiry</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>
                            {{ $item->product->name }}
                            @if($item->is_prescription_item)
                                <div><small>Rx item</small></div>
                            @endif
                        </td>
                        <td>{{ $item->batch->batch_no }} / {{ $item->batch->expiry_date->format('Y-m-d') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td>{{ number_format((float) $sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ number_format((float) $sale->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td>{{ number_format((float) $sale->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Total</td>
                <td>{{ number_format((float) $sale->total, 2) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td>{{ number_format((float) $sale->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Change</td>
                <td>{{ number_format((float) $sale->change_amount, 2) }}</td>
            </tr>
        </table>

        <div>
            <strong>Payments</strong>
            <ul>
                @foreach($sale->payments as $payment)
                    <li>{{ ucfirst($payment->method->value) }}: {{ number_format((float) $payment->amount, 2) }}</li>
                @endforeach
            </ul>
        </div>

        <div class="receipt-footer">
            @if(! empty($settings->receipt_footer))
                <p>{!! nl2br(e($settings->receipt_footer)) !!}</p>
            @else
                <p>Thank you for your purchase.</p>
            @endif
        </div>
    </div>
</body>
</html>
