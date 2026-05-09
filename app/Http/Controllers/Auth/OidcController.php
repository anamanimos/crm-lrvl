<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OidcController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('oidc')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $socialUser = Socialite::driver('oidc')->stateless()->user();
            
            \Illuminate\Support\Facades\Log::info('OIDC Social User Data:', [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'nickname' => $socialUser->getNickname(),
                'raw' => $socialUser->getRaw(),
            ]);

            // Find or create user
            $user = User::where('oidc_sub', $socialUser->getId())
                        ->orWhere('email', $socialUser->getEmail())
                        ->first();

            if (!$user) {
                // Create user if not exists
                $name = $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail();
                $username = $socialUser->getNickname() ?? $socialUser->getRaw()['preferred_username'] ?? explode('@', $socialUser->getEmail())[0];

                $user = User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $socialUser->getEmail(),
                    'oidc_sub' => $socialUser->getId(),
                    'password' => bcrypt(str()->random(24)),
                    'role_id' => 3, // Default role: CS (low access)
                    'is_active' => false, // Initially inactive
                    'email_verified_at' => now(),
                ]);
                
                \Illuminate\Support\Facades\Log::info('New OIDC user created (inactive):', ['id' => $user->id, 'email' => $user->email]);
            } else {
                // Update OIDC sub if not set
                if (empty($user->oidc_sub)) {
                    $user->oidc_sub = $socialUser->getId();
                    $user->save();
                }
                
                \Illuminate\Support\Facades\Log::info('Existing user logged in via OIDC:', ['id' => $user->id, 'email' => $user->email]);
            }

            Auth::login($user, true);

            if (!$user->is_active) {
                return redirect()->route('auth.claim-admin');
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OIDC Callback Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => request()->all()
            ]);
            return redirect()->route('login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    public function showClaimForm()
    {
        if (Auth::user()->is_active) {
            return redirect()->route('dashboard');
        }

        return view('auth.claim-admin');
    }

    public function claimAdmin(Request $request)
    {
        $request->validate([
            'secret_code' => 'required|string',
        ]);

        if ($request->secret_code === env('ADMIN_CLAIM_CODE')) {
            $user = Auth::user();
            $user->is_active = true;
            $user->role_id = 1; // Super Admin
            $user->save();

            return redirect()->route('dashboard')->with('success', 'Selamat! Anda sekarang adalah Super Admin.');
        }

        return back()->withErrors(['secret_code' => 'Kode rahasia salah. Silakan hubungi admin utama.']);
    }
}
