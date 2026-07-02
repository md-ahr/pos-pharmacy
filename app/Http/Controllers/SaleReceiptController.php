<?php

namespace App\Http\Controllers;

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Services\TenantSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SaleReceiptController extends Controller
{
    public function show(Sale $sale): View
    {
        $this->authorizeReceipt($sale);

        return view('sales.receipt', $this->receiptData($sale));
    }

    public function pdf(Sale $sale): Response
    {
        $this->authorizeReceipt($sale);

        $data = $this->receiptData($sale);
        $data['forPdf'] = true;

        return Pdf::loadView('sales.receipt', $data)
            ->download('receipt-'.$sale->invoice_no.'.pdf');
    }

    private function authorizeReceipt(Sale $sale): void
    {
        abort_unless(
            $sale->status === SaleStatus::Completed
                && auth()->user()?->tenant_id === $sale->tenant_id,
            HttpResponse::HTTP_FORBIDDEN,
        );
    }

    /**
     * @return array{sale: Sale, settings: mixed}
     */
    private function receiptData(Sale $sale): array
    {
        $sale->load([
            'items.product',
            'items.batch',
            'items.productUnit',
            'payments',
            'branch',
            'cashier',
            'customer',
            'tenant',
        ]);

        return [
            'sale' => $sale,
            'settings' => app(TenantSettingsService::class)->forTenant($sale->tenant_id),
        ];
    }
}
