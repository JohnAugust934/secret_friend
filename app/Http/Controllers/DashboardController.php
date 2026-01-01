<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $groups = $user->groups()->orderByDesc('created_at')->get();

        return view('dashboard', compact('groups'));
    }
}
