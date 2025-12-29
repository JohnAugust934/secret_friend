<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rotas que exigem Login
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        // Busca os grupos onde o usuário é dono OU membro
        $groups = auth()->user()->groups()->orderByDesc('created_at')->get();

        return view('dashboard', compact('groups'));
    })->middleware(['auth', 'verified'])->name('dashboard');

    // Nossas rotas de Grupos
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

    // Rotas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas de Convite
    Route::get('/invite/{token}', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/invite/{token}', [GroupController::class, 'joinStore'])->name('groups.join.store');

    Route::post('/groups/{group}/draw', [GroupController::class, 'draw'])->name('groups.draw');

    // Rota para atualizar apenas a wishlist
    Route::put('/groups/{group}/wishlist', [GroupController::class, 'updateWishlist'])->name('groups.wishlist.update');

    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
});

// Esta é a linha que estava dando erro, agora o arquivo existirá:
require __DIR__ . '/auth.php';
