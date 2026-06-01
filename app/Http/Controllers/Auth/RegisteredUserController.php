<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        if (request()->filled('invite_token')) {
            session(['invite_token' => request()->string('invite_token')->toString()]);
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Dispara o evento Registered, que aciona o envio SÍNCRONO do e-mail de
        // verificação via notifyNow() (VerifyEmailQueued). O try/catch garante
        // que uma falha temporária de SMTP nunca resulte em um erro 500 para
        // o usuário — ele é redirecionado normalmente e pode solicitar o
        // reenvio na tela de verificação de e-mail.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de verificação após cadastro.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        Auth::login($user);

        $inviteToken = $request->string('invite_token')->toString();
        if ($inviteToken === '') {
            $inviteToken = (string) $request->session()->pull('invite_token', '');
        }

        if ($inviteToken !== '') {
            $request->session()->put('invite_token', $inviteToken);

            return redirect()->route('groups.join', $inviteToken);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
