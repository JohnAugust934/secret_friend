<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // PERFORMANCE: Eager load dos relacionamentos acessados na view
        // para evitar N+1 queries ao iterar sobre os grupos do usuário.
        $groups = $user->groups()
            ->with(['owner', 'members'])
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard', compact('groups'));
    }
}
