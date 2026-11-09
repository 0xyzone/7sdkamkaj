<?php

declare(strict_types=1);

use App\Http\Controllers\AccountDeactivatedController;
use App\Http\Controllers\LabelStudioController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/account-deactivated', [AccountDeactivatedController::class, 'show'])->name('account.deactivated');
    Route::post('/account-deactivated/logout', [AccountDeactivatedController::class, 'logout'])->name('account.deactivated.logout');

    // Standalone Photoshop-style Label Studio workstation (outside the Filament panel)
    Route::middleware(['auth', EnsureUserIsActive::class])->prefix('label-studio')->name('label-studio.')->group(function () {
        Route::post('/save', [LabelStudioController::class, 'save'])->name('save');
        Route::post('/print', [LabelStudioController::class, 'print'])->name('print');
        Route::get('/printers/{printer}/status', [LabelStudioController::class, 'printerStatus'])->name('printer-status');
        Route::get('/{id?}', [LabelStudioController::class, 'index'])->whereNumber('id')->name('index');
    });
});
