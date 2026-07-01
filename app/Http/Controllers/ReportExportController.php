<?php

namespace App\Http\Controllers;

use App\Data\ReportFilters;
use App\Exports\ArrayReportExport;
use App\Services\Reports\ExpiryReportService;
use App\Services\Reports\InventoryValuationReportService;
use App\Services\Reports\ProfitMarginReportService;
use App\Services\Reports\SalesReportService;
use App\Services\Reports\TaxReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function pdf(Request $request): Response
    {
        $payload = $this->buildPayload($request);

        return Pdf::loadView($payload['view'], $payload['data'])
            ->download($payload['filename']);
    }

    public function excel(Request $request): BinaryFileResponse
    {
        $payload = $this->buildPayload($request);

        return Excel::download(
            new ArrayReportExport($payload['title'], $payload['headings'], $payload['rows']),
            str_replace('.pdf', '.xlsx', $payload['filename']),
        );
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function buildPayload(Request $request): array
    {
        $type = (string) $request->query('type', 'sales');

        return match ($type) {
            'sales' => $this->salesPayload($request),
            'profit-margin' => $this->profitMarginPayload($request),
            'inventory-valuation' => $this->inventoryValuationPayload($request),
            'expiry' => $this->expiryPayload($request),
            'tax' => $this->taxPayload($request),
            default => abort(404, 'Unknown report type.'),
        };
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function salesPayload(Request $request): array
    {
        $filters = ReportFilters::fromArray($request->query());
        $service = app(SalesReportService::class);
        $summary = $service->summary($filters);
        $productBreakdown = $service->productBreakdown($filters);

        $rows = $productBreakdown->map(fn ($row): array => [
            $row->product_name,
            $row->quantity_sold,
            $row->revenue,
        ])->all();

        return [
            'view' => 'reports.pdf.table',
            'data' => [
                'title' => 'Sales Report',
                'period' => $this->periodLabel($filters),
                'summary' => $summary,
                'headings' => ['Product', 'Qty Sold (base)', 'Revenue'],
                'rows' => $rows,
            ],
            'title' => 'Sales Report',
            'headings' => ['Product', 'Qty Sold (base)', 'Revenue'],
            'rows' => $rows,
            'filename' => 'sales-report-'.$filters->from->toDateString().'.pdf',
        ];
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function profitMarginPayload(Request $request): array
    {
        $filters = ReportFilters::fromArray($request->query());
        $service = app(ProfitMarginReportService::class);
        $summary = $service->summary($filters);
        $items = $service->rows($filters);

        $rows = $items->map(fn ($row): array => [
            $row->product_name,
            $row->quantity_sold,
            $row->revenue,
            $row->cost,
            $row->profit,
            $row->margin_percent.'%',
        ])->all();

        return [
            'view' => 'reports.pdf.table',
            'data' => [
                'title' => 'Profit Margin Report',
                'period' => $this->periodLabel($filters),
                'summary' => $summary,
                'headings' => ['Product', 'Qty Sold', 'Revenue', 'Cost', 'Profit', 'Margin %'],
                'rows' => $rows,
            ],
            'title' => 'Profit Margin',
            'headings' => ['Product', 'Qty Sold', 'Revenue', 'Cost', 'Profit', 'Margin %'],
            'rows' => $rows,
            'filename' => 'profit-margin-'.$filters->from->toDateString().'.pdf',
        ];
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function inventoryValuationPayload(Request $request): array
    {
        $branchId = filled($request->query('branch_id')) ? (int) $request->query('branch_id') : null;
        $service = app(InventoryValuationReportService::class);
        $summary = $service->summary($branchId);
        $items = $service->rows($branchId);

        $rows = $items->map(fn ($row): array => [
            $row->product_name,
            $row->quantity,
            $row->base_unit,
            $row->value,
        ])->all();

        return [
            'view' => 'reports.pdf.table',
            'data' => [
                'title' => 'Inventory Valuation',
                'period' => 'As of '.today()->toDateString(),
                'summary' => $summary,
                'headings' => ['Product', 'Quantity', 'Unit', 'Value'],
                'rows' => $rows,
            ],
            'title' => 'Inventory Valuation',
            'headings' => ['Product', 'Quantity', 'Unit', 'Value'],
            'rows' => $rows,
            'filename' => 'inventory-valuation-'.today()->toDateString().'.pdf',
        ];
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function expiryPayload(Request $request): array
    {
        $filters = ReportFilters::fromArray($request->query());
        $service = app(ExpiryReportService::class);
        $items = $service->rows($filters);

        $rows = $items->map(fn ($row): array => [
            $row->product_name,
            $row->batch_no,
            $row->branch_name,
            $row->expiry_date,
            $row->quantity.' '.$row->base_unit,
            $row->status === 'expired' ? 'Expired' : 'Expiring Soon',
        ])->all();

        return [
            'view' => 'reports.pdf.table',
            'data' => [
                'title' => 'Expiry Report',
                'period' => 'Within '.$filters->expiryDaysAhead.' days',
                'summary' => ['rows' => count($rows)],
                'headings' => ['Product', 'Batch', 'Branch', 'Expiry', 'Quantity', 'Status'],
                'rows' => $rows,
            ],
            'title' => 'Expiry Report',
            'headings' => ['Product', 'Batch', 'Branch', 'Expiry', 'Quantity', 'Status'],
            'rows' => $rows,
            'filename' => 'expiry-report-'.today()->toDateString().'.pdf',
        ];
    }

    /**
     * @return array{
     *     view: string,
     *     data: array<string, mixed>,
     *     title: string,
     *     headings: list<string>,
     *     rows: list<array<int, string|int|float|null>>,
     *     filename: string
     * }
     */
    private function taxPayload(Request $request): array
    {
        $filters = ReportFilters::fromArray($request->query());
        $service = app(TaxReportService::class);
        $summary = $service->summary($filters);
        $items = $service->dailyBreakdown($filters);

        $rows = $items->map(fn ($row): array => [
            $row->period,
            $row->sales_count,
            $row->taxable_sales,
            $row->tax_total,
        ])->all();

        return [
            'view' => 'reports.pdf.table',
            'data' => [
                'title' => 'Tax Report',
                'period' => $this->periodLabel($filters),
                'summary' => $summary,
                'headings' => ['Date', 'Sales', 'Taxable Sales', 'Tax'],
                'rows' => $rows,
            ],
            'title' => 'Tax Report',
            'headings' => ['Date', 'Sales', 'Taxable Sales', 'Tax'],
            'rows' => $rows,
            'filename' => 'tax-report-'.$filters->from->toDateString().'.pdf',
        ];
    }

    private function periodLabel(ReportFilters $filters): string
    {
        return $filters->from->toDateString().' to '.$filters->to->toDateString();
    }
}
