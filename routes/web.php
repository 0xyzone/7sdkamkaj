<?php

declare(strict_types=1);

use App\Http\Controllers\AccountDeactivatedController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/account-deactivated', [AccountDeactivatedController::class, 'show'])->name('account.deactivated');
    Route::post('/account-deactivated/logout', [AccountDeactivatedController::class, 'logout'])->name('account.deactivated.logout');
});
