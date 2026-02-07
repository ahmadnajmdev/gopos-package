<?php

use Gopos\Http\Controllers\CustomerStatementController;
use Gopos\Http\Controllers\DocumentationController;
use Gopos\Http\Livewire\Install\InstallWizard;
use Gopos\Http\Livewire\InvoicePDF;
use Gopos\Http\Livewire\SaleInvoice;

// Installation Route
Route::get('/install', InstallWizard::class)->name('install');

// Documentation Routes (public access)
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocumentationController::class, 'index'])->name('index');
    Route::get('/{slug}', [DocumentationController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'lang'])->group(function () {
    Route::get('print-invoice/{purchase}', InvoicePDF::class)->name('print-invoice');
    Route::get('print-sale-invoice/{sale}', SaleInvoice::class)->name('print-sale-invoice');
    Route::get('customer-statement-print/{customer}', [CustomerStatementController::class, 'print'])->name('customer.statement.print');
    Route::get('customer-statement-download/{customer}/{filename}', [CustomerStatementController::class, 'download'])->name('customer.statement.download');
});
