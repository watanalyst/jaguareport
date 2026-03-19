<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Relatorios\Financeiro\FechamentoCambioController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRedeconomiaController;
use App\Http\Controllers\Relatorios\Financeiro\ComissaoRepresentanteController;
use App\Http\Controllers\Relatorios\Exportacao\EmbarquesExportacaoController;
use App\Http\Controllers\Relatorios\Exportacao\PackingListController;
use App\Http\Controllers\Relatorios\Exportacao\BaixaDisponivelController;
use App\Http\Controllers\Relatorios\Exportacao\BancoCreditNoteController;
use App\Http\Controllers\Relatorios\Exportacao\CambioPeriodoController;
use App\Http\Controllers\Relatorios\Exportacao\ComissaoExportacaoController;
use App\Http\Controllers\Relatorios\Exportacao\NotasFiscaisExportacaoController;
use App\Http\Controllers\Relatorios\Exportacao\CreditNoteController;
use App\Http\Controllers\Relatorios\Exportacao\DebitNoteController;
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
            ->middleware('report.permission:blank_COMISSAO');
        Route::get('comissao/gerar', [ComissaoController::class, 'gerar'])
            ->name('comissao.gerar')
            ->middleware('report.permission:blank_COMISSAO');

        Route::get('comissao-redeconomia', [ComissaoRedeconomiaController::class, 'index'])
            ->name('comissao_redeconomia')
            ->middleware('report.permission:blank_COMISSAO_REDECONOMIA');
        Route::get('comissao-redeconomia/gerar', [ComissaoRedeconomiaController::class, 'gerar'])
            ->name('comissao_redeconomia.gerar')
            ->middleware('report.permission:blank_COMISSAO_REDECONOMIA');

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

        // Packing List (CRUD)
        Route::middleware('report.permission:blank_FORM_PACKING_LIST')->group(function () {
            Route::get('packing-list', [PackingListController::class, 'index'])
                ->name('packing_list');
            Route::get('packing-list/pesquisar', [PackingListController::class, 'pesquisar'])
                ->name('packing_list.pesquisar');
            Route::get('packing-list/lookup', [PackingListController::class, 'lookup'])
                ->name('packing_list.lookup');
            Route::get('packing-list/{id}', [PackingListController::class, 'show'])
                ->name('packing_list.show');
            Route::post('packing-list', [PackingListController::class, 'store'])
                ->name('packing_list.store');
            Route::put('packing-list/{id}', [PackingListController::class, 'update'])
                ->name('packing_list.update');
            Route::delete('packing-list/{id}', [PackingListController::class, 'destroy'])
                ->name('packing_list.destroy');

            // Detail rows
            Route::post('packing-list/{masterId}/detalhes', [PackingListController::class, 'storeDetail'])
                ->name('packing_list.detalhes.store');
            Route::put('packing-list/{masterId}/detalhes/{detailId}', [PackingListController::class, 'updateDetail'])
                ->name('packing_list.detalhes.update');
            Route::delete('packing-list/{masterId}/detalhes/{detailId}', [PackingListController::class, 'destroyDetail'])
                ->name('packing_list.detalhes.destroy');
        });

        // Comissão Exportação
        Route::get('comissao-exportacao', [ComissaoExportacaoController::class, 'index'])
            ->name('comissao_exportacao')
            ->middleware('report.permission:blank_COMISSAO_EXPORTACAO');
        Route::get('comissao-exportacao/pesquisar', [ComissaoExportacaoController::class, 'pesquisar'])
            ->name('comissao_exportacao.pesquisar')
            ->middleware('report.permission:blank_COMISSAO_EXPORTACAO');
        Route::get('comissao-exportacao/agentes', [ComissaoExportacaoController::class, 'agentes'])
            ->name('comissao_exportacao.agentes')
            ->middleware('report.permission:blank_COMISSAO_EXPORTACAO');

        // Credit Note
        Route::get('credit-note', [CreditNoteController::class, 'index'])
            ->name('credit_note')
            ->middleware('report.permission:blank_CREDIT_NOTE');
        Route::get('credit-note/gerar', [CreditNoteController::class, 'gerar'])
            ->name('credit_note.gerar')
            ->middleware('report.permission:blank_CREDIT_NOTE');

        // Baixa Disponível
        Route::get('baixa-disponivel', [BaixaDisponivelController::class, 'index'])
            ->name('baixa_disponivel')
            ->middleware('report.permission:blank_BAIXA_DISPONIVEL');
        Route::get('baixa-disponivel/pesquisar', [BaixaDisponivelController::class, 'pesquisar'])
            ->name('baixa_disponivel.pesquisar')
            ->middleware('report.permission:blank_BAIXA_DISPONIVEL');

        // Banco Credit Note (CRUD)
        Route::middleware('report.permission:blank_BANCO_CREDITO_NOTE')->group(function () {
            Route::get('banco-credit-note', [BancoCreditNoteController::class, 'index'])
                ->name('banco_credit_note');
            Route::get('banco-credit-note/pesquisar', [BancoCreditNoteController::class, 'pesquisar'])
                ->name('banco_credit_note.pesquisar');
            Route::get('banco-credit-note/{id}', [BancoCreditNoteController::class, 'show'])
                ->name('banco_credit_note.show');
            Route::post('banco-credit-note', [BancoCreditNoteController::class, 'store'])
                ->name('banco_credit_note.store');
            Route::put('banco-credit-note/{id}', [BancoCreditNoteController::class, 'update'])
                ->name('banco_credit_note.update');
            Route::delete('banco-credit-note/{id}', [BancoCreditNoteController::class, 'destroy'])
                ->name('banco_credit_note.destroy');
        });

        // Debit Note
        Route::get('debit-note', [DebitNoteController::class, 'index'])
            ->name('debit_note')
            ->middleware('report.permission:blank_DEBIT_NOTE');
        Route::get('debit-note/gerar', [DebitNoteController::class, 'gerar'])
            ->name('debit_note.gerar')
            ->middleware('report.permission:blank_DEBIT_NOTE');

        // Notas Fiscais Exportação
        Route::get('notas-fiscais-exportacao', [NotasFiscaisExportacaoController::class, 'index'])
            ->name('notas_fiscais_exportacao')
            ->middleware('report.permission:blank_NOTAS_FISCAIS_EXPORTACAO');
        Route::get('notas-fiscais-exportacao/pesquisar', [NotasFiscaisExportacaoController::class, 'pesquisar'])
            ->name('notas_fiscais_exportacao.pesquisar')
            ->middleware('report.permission:blank_NOTAS_FISCAIS_EXPORTACAO');

        // Câmbio Período
        Route::get('cambio-periodo', [CambioPeriodoController::class, 'index'])
            ->name('cambio_periodo')
            ->middleware('report.permission:blank_CAMBIO_PERIODO');
        Route::get('cambio-periodo/pesquisar', [CambioPeriodoController::class, 'pesquisar'])
            ->name('cambio_periodo.pesquisar')
            ->middleware('report.permission:blank_CAMBIO_PERIODO');
    });
});

require __DIR__.'/auth.php';
