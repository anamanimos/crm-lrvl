<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $logoutUrl = config('services.oidc.logout_url');
        if ($logoutUrl && $user && !empty($user->oidc_sub)) {
            $redirectUri = url('/');
            // Build logout URL with redirect back
            $separator = str_contains($logoutUrl, '?') ? '&' : '?';
            return redirect($logoutUrl . $separator . 'post_logout_redirect_uri=' . urlencode($redirectUri));
        }

        return redirect('/');
    }
}
