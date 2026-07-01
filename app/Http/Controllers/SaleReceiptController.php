<?php

namespace App\Http\Controllers;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class SaleReceiptController extends Controller
{
    public function show(Sale $sale): View
    {
        abort_unless(
            $sale->status === SaleStatus::Completed
                && auth()->user()?->tenant_id === $sale->tenant_id,
            Response::HTTP_FORBIDDEN,
        );

        $sale->load([
            'items.product',
            'items.batch',
            'items.productUnit',
            'payments',
            'branch',
            'cashier',
            'tenant',
        ]);

        return view('sales.receipt', [
            'sale' => $sale,
        ]);
    }
}
