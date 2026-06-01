<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // O envio agora é síncrono (notifyNow). O try/catch garante que
        // uma falha temporária de SMTP não resulte em erro 500 para o
        // usuário — ele recebe o feedback de "link enviado" e pode tentar
        // novamente em instantes.
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Falha ao reenviar e-mail de verificação.', [
                'user_id' => $request->user()->id,
                'email'   => $request->user()->email,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
