<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Relatorios\Financeiro\FechamentoCambioController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRedeconomiaController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRepresentanteController;
use App\Http\Controllers\Relatorios\Exportacao\EmbarquesExportacaoController;
use App\Http\Controllers\Relatorios\Exportacao\ProcessosExportacaoController;
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

        Route::get('comissao-representante', [ComissaoRepresentanteController::class, 'index'])
            ->name('comissao_representante')
            ->middleware('report.permission:blank_COMISSAO_REPRESENTANTE');
        Route::get('comissao-representante/pesquisar', [ComissaoRepresentanteController::class, 'pesquisar'])
            ->name('comissao_representante.pesquisar')
            ->middleware('report.permission:blank_COMISSAO_REPRESENTANTE');
        Route::post('comissao-representante/aprovar', [ComissaoRepresentanteController::class, 'aprovar'])
            ->name('comissao_representante.aprovar')
            ->middleware('report.permission:blank_COMISSAO_REPRESENTANTE');
        Route::post('comissao-representante/desaprovar', [ComissaoRepresentanteController::class, 'desaprovar'])
            ->name('comissao_representante.desaprovar')
            ->middleware('report.permission:blank_COMISSAO_REPRESENTANTE');

        Route::get('fechamento-cambio', [FechamentoCambioController::class, 'index'])
            ->name('fechamento_cambio')
            ->middleware('report.permission:blank_FECHAMENTO_CAMBIO');
        Route::get('fechamento-cambio/gerar', [FechamentoCambioController::class, 'gerar'])
            ->name('fechamento_cambio.gerar')
            ->middleware('report.permission:blank_FECHAMENTO_CAMBIO');
    });

    // Exportação
    Route::prefix('exportacao')->name('exportacao.')->group(function () {

        Route::get('processos-exportacao', [ProcessosExportacaoController::class, 'index'])
            ->name('processos_exportacao')
            ->middleware('report.permission:blank_PROCESSOS_EXPORTACAO');
        Route::get('processos-exportacao/pesquisar', [ProcessosExportacaoController::class, 'pesquisar'])
            ->name('processos_exportacao.pesquisar')
            ->middleware('report.permission:blank_PROCESSOS_EXPORTACAO');
        Route::get('processos-exportacao/documento', [ProcessosExportacaoController::class, 'documento'])
            ->name('processos_exportacao.documento')
            ->middleware('report.permission:blank_PROCESSOS_EXPORTACAO');

        Route::get('embarques-exportacao', [EmbarquesExportacaoController::class, 'index'])
            ->name('embarques_exportacao')
            ->middleware('report.permission:blank_EMBARQUES_EXPORTACAO');
        Route::get('embarques-exportacao/pesquisar', [EmbarquesExportacaoController::class, 'pesquisar'])
            ->name('embarques_exportacao.pesquisar')
            ->middleware('report.permission:blank_EMBARQUES_EXPORTACAO');
        Route::get('embarques-exportacao/items', [EmbarquesExportacaoController::class, 'items'])
            ->name('embarques_exportacao.items')
            ->middleware('report.permission:blank_EMBARQUES_EXPORTACAO');
    });
});

require __DIR__.'/auth.php';
