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
        $groups = auth()->user()->groups()->orderByDesc('created_at')->get();
        return view('dashboard', compact('groups'));
    })->middleware(['auth', 'verified'])->name('dashboard');

    // Grupos
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    
    // --- ROTAS DE EDIÇÃO ---
    Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Convites
    Route::get('/invite/{token}', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/invite/{token}', [GroupController::class, 'joinStore'])->name('groups.join.store');

    // Sorteio
    Route::post('/groups/{group}/draw', [GroupController::class, 'draw'])->name('groups.draw');

    // Wishlist
    Route::put('/groups/{group}/wishlist', [GroupController::class, 'updateWishlist'])->name('groups.wishlist.update');

    // --- NOVA ROTA (ADICIONAR ESTA LINHA) ---
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.destroy');

    // Excluir Grupo
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
});

require __DIR__ . '/auth.php';
