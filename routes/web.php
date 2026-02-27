<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Relatorios\Financeiro\FechamentoCambioController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRedeconomiaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware('sc.auth')->name('dashboard');

// Profile (Breeze) - protegido por auth
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Relatórios - auto-login via ScriptCase
Route::prefix('relatorios')->name('relatorios.')->middleware('sc.auth')->group(function () {

    // Financeiro
    Route::prefix('financeiro')->name('financeiro.')->group(function () {

        Route::get('comissao', [ComissaoController::class, 'index'])
            ->name('comissao')
            ->middleware('report.permission:blank_COMISSOES');
        Route::get('comissao/gerar', [ComissaoController::class, 'gerar'])
            ->name('comissao.gerar')
            ->middleware('report.permission:blank_COMISSOES');

        Route::get('comissao-redeconomia', [ComissaoRedeconomiaController::class, 'index'])
            ->name('comissao_redeconomia')
            ->middleware('report.permission:blank_COMISSOES');
        Route::get('comissao-redeconomia/gerar', [ComissaoRedeconomiaController::class, 'gerar'])
            ->name('comissao_redeconomia.gerar')
            ->middleware('report.permission:blank_COMISSOES');

        Route::get('fechamento-cambio', [FechamentoCambioController::class, 'index'])
            ->name('fechamento_cambio')
            ->middleware('report.permission:FILTRO_FECHAMENTO_CAMBIO');
        Route::get('fechamento-cambio/gerar', [FechamentoCambioController::class, 'gerar'])
            ->name('fechamento_cambio.gerar')
            ->middleware('report.permission:FILTRO_FECHAMENTO_CAMBIO');
    });
});

require __DIR__.'/auth.php';
