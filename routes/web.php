<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rotas Autenticadas e Verificadas
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        $groups = auth()->user()->groups()->orderByDesc('created_at')->get();
        return view('dashboard', compact('groups'));
    })->name('dashboard');

    // -- Rotas de Grupos --

    // CRUD Básico (Create, Store, Show, Edit, Update, Destroy)
    Route::resource('groups', GroupController::class)->except(['index']);

    // Funcionalidades Específicas do Grupo
    Route::post('/groups/{group}/draw', [GroupController::class, 'draw'])->name('groups.draw');
    Route::put('/groups/{group}/wishlist', [GroupController::class, 'updateWishlist'])->name('groups.wishlist.update');
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.destroy');

    // Entrar no Grupo (Ação do Formulário)
    Route::post('/invite/{token}', [GroupController::class, 'joinStore'])->name('groups.join.store');

    // Atualização em Tempo Real (Polling)
    Route::get('/groups/{group}/members-list', [GroupController::class, 'membersList'])->name('groups.members.list');

    // --- NOVAS ROTAS DE RESTRIÇÕES (EXCLUSÕES) ---
    Route::post('/groups/{group}/exclusions', [GroupController::class, 'storeExclusion'])->name('groups.exclusions.store');
    Route::delete('/groups/{group}/exclusions/{exclusion}', [GroupController::class, 'destroyExclusion'])->name('groups.exclusions.destroy');

    // -- Rotas de Perfil --
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rota Pública de Convite (Exibe a tela de entrar)
Route::get('/invite/{token}', [GroupController::class, 'join'])->middleware(['auth', 'verified'])->name('groups.join');

require __DIR__ . '/auth.php';
