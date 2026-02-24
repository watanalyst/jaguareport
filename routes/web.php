<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRedeconomiaController;
use Illuminate\Support\Facades\Route;

// Redireciona para o portal de relatórios
Route::get('/', function () {
    return redirect()->route('relatorios.financeiro.comissao');
});

Route::get('/dashboard', function () {
    return redirect()->route('relatorios.financeiro.comissao');
})->name('dashboard');

// Profile (Breeze) - protegido por auth
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Relatórios - com auto-login via ScriptCase
Route::prefix('relatorios')->name('relatorios.')->middleware('sc.auth')->group(function () {

    // Financeiro
    Route::prefix('financeiro')->name('financeiro.')->group(function () {

        Route::get('comissao', [ComissaoController::class, 'index'])
            ->name('comissao');
        Route::get('comissao/gerar', [ComissaoController::class, 'gerar'])
            ->name('comissao.gerar');

        Route::get('comissao-redeconomia', [ComissaoRedeconomiaController::class, 'index'])
            ->name('comissao_redeconomia');
        Route::get('comissao-redeconomia/gerar', [ComissaoRedeconomiaController::class, 'gerar'])
            ->name('comissao_redeconomia.gerar');
    });
});

require __DIR__.'/auth.php';
