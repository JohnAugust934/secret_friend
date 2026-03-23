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
        if (request()->filled('invite_token')) {
            session(['invite_token' => request()->string('invite_token')->toString()]);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $inviteToken = $request->string('invite_token')->toString();
        if ($inviteToken === '') {
            $inviteToken = (string) $request->session()->pull('invite_token', '');
        }

        if ($inviteToken !== '') {
            $request->session()->put('invite_token', $inviteToken);

            return redirect()->route('groups.join', $inviteToken);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Mude de redirect('/') para redirect('/login')
        return redirect('/login');
    }
}
