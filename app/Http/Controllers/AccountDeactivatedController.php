<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountDeactivatedController extends Controller
{
    /**
     * Display the account deactivated notice.
     */
    public function show(): View|RedirectResponse
    {
        $user = Filament::auth()->user() ?? auth()->user();

        if (! $user) {
            return redirect()->to(Filament::getLoginUrl() ?? '/login');
        }

        if ($user->is_active) {
            return redirect()->to(Filament::getUrl() ?? '/');
        }

        return view('auth.account-deactivated', [
            'user' => $user,
        ]);
    }

    /**
     * Log out the deactivated user and return to login screen.
     */
    public function logout(Request $request): RedirectResponse
    {
        Filament::auth()->logout();
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(Filament::getLoginUrl() ?? '/login');
    }
}
