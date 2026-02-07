<?php

namespace Gopos\Http\Controllers;

use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerStatementController extends Controller
{
    public function print(Request $request, Customer $customer): View
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $status = $request->get('status', 'all');

        // Get sales based on filters
        $salesQuery = $customer->sales()->getQuery();

        // Apply date filters
        if ($fromDate) {
            $salesQuery->whereDate('sale_date', '>=', $fromDate);
        }
        if ($toDate) {
            $salesQuery->whereDate('sale_date', '<=', $toDate);
        }

        // Apply status filter
        if ($status && $status !== 'all') {
            switch ($status) {
                case 'paid':
                    $salesQuery->whereRaw('paid_amount >= total_amount');
                    break;
                case 'partial':
                    $salesQuery->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
                    break;
                case 'unpaid':
                    $salesQuery->whereRaw('paid_amount = 0');
                    break;
            }
        }

        $sales = $salesQuery->orderBy('sale_date', 'desc')->get();

        // Calculate summary
        $totalSales = $sales->sum('amount_in_base_currency');
        $totalPaid = $sales->sum(function ($sale) {
            if ($sale->currency_id == Currency::getBaseCurrency()->id) {
                return $sale->paid_amount;
            }

            return $sale->currency->convertFromCurrency($sale->paid_amount, $sale->currency->code);
        });
        $totalBalance = $totalSales - $totalPaid;

        $summary = [
            'customer' => $customer,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'total_invoices' => $sales->count(),
            'paid_invoices' => $sales->where('paid_amount', '>=', 'total_amount')->count(),
            'unpaid_invoices' => $sales->where('paid_amount', 0)->count(),
            'partial_invoices' => $sales->where('paid_amount', '>', 0)->where('paid_amount', '<', 'total_amount')->count(),
        ];

        // Generate PDF
        return view('gopos::filament.resources.customers.pages.customer-statement-print', [
            'customer' => $customer,
            'summary' => $summary,
            'sales' => $sales,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'baseCurrency' => Currency::getBaseCurrency(),
        ]);
    }

    public function download(Request $request, Customer $customer, string $filename): BinaryFileResponse
    {
        $tempPath = storage_path('app/temp/'.$filename);

        if (! file_exists($tempPath)) {
            abort(404, 'File not found');
        }

        $response = response()->file($tempPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);

        // Clean up the temporary file after download
        register_shutdown_function(function () use ($tempPath) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        });

        return $response;
    }
}
