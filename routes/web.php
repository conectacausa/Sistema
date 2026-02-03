<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cargo\CboController;
use App\Http\Controllers\Cargo\CargoController;
use App\Http\Controllers\Recrutamento\FluxoAprovacaoController;
use App\Http\Controllers\Cargo\HeadcountController;
use App\Http\Controllers\Config\UsuariosController;
use App\Http\Controllers\Config\GrupoPermissaoController;
use App\Http\Controllers\Config\FiliaisController;
use App\Http\Controllers\Beneficios\BolsaEstudosController;
use App\Http\Controllers\Beneficios\BolsaDocumentosController;
use App\Http\Controllers\Beneficios\BolsaRelatoriosController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'tenant'])
    ->group(function () {

        Route::get('/', function () {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            return redirect()->route('dashboard');
        })->name('home');

        Route::get('/login', [LoginController::class, 'show'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.post');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::middleware(['auth', 'tenant.user'])->group(function () {

            Route::get('/dashboard', fn () => view('dashboard.index'))
                ->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES - FILIAIS 
            |--------------------------------------------------------------------------
            */

           Route::get('/config/filiais', [FiliaisController::class, 'index'])
                ->middleware('screen:5')
                ->name('config.filiais.index');
            
            Route::get('/config/filiais/novo', [FiliaisController::class, 'create'])
                ->middleware('screen:5')
                ->name('config.filiais.create');
            
            Route::get('/config/filiais/{id}/editar', [FiliaisController::class, 'edit'])
                ->whereNumber('id')
                ->middleware('screen:5')
                ->name('config.filiais.edit');
            
            Route::delete('/config/filiais/{id}', [FiliaisController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware('screen:5')
                ->name('config.filiais.destroy');
            
            Route::get('/config/filiais/grid', [FiliaisController::class, 'grid'])
                ->middleware('screen:5')
                ->name('config.filiais.grid');
            
            Route::get('/config/filiais/paises', [FiliaisController::class, 'paises'])
                ->middleware('screen:5')
                ->name('config.filiais.paises');
            
            Route::get('/config/filiais/estados', [FiliaisController::class, 'estados'])
                ->middleware('screen:5')
                ->name('config.filiais.estados');
            
            Route::get('/config/filiais/cidades', [FiliaisController::class, 'cidades'])
                ->middleware('screen:5')
                ->name('config.filiais.cidades');

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES - USUÁRIOS
            |--------------------------------------------------------------------------
            */
            
            Route::get('/config/usuarios', [UsuariosController::class, 'index'])
                ->middleware('screen:10')
                ->name('config.usuarios.index');
            
             Route::get('/config/usuarios/novo', [UsuariosController::class, 'create'])
                ->middleware('screen:10')
                ->name('config.usuarios.create');

            Route::post('/config/usuarios', [UsuariosController::class, 'store'])
                ->middleware('screen:10')
                ->name('config.usuarios.store');

            Route::get('/config/usuarios/{id}/editar', [UsuariosController::class, 'edit'])
                ->whereNumber('id')
                ->name('config.usuarios.edit');

            Route::put('/config/usuarios/{id}', [UsuariosController::class, 'update'])
                ->whereNumber('id')
                ->middleware('screen:10')
                ->name('config.usuarios.update');
            
            Route::delete('/config/usuarios/{id}', [UsuariosController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware('screen:10')
                ->name('config.usuarios.destroy');
            
            Route::post('/config/usuarios/{id}/inativar', [UsuariosController::class, 'inativar'])
                ->whereNumber('id')
                ->middleware('screen:10')
                ->name('config.usuarios.inativar');

            Route::get('/config/usuarios/setores-por-filial', [UsuariosController::class, 'setoresPorFilial'])
                ->whereNumber('filial_id')
                ->middleware('screen:10')
                ->name('config.usuarios.setores_por_filial');
            
            Route::get('/config/usuarios/lotacoes-grid', [UsuariosController::class, 'lotacoesGrid'])
                ->middleware('screen:10')
                ->name('config.usuarios.lotacoes_grid');
            
            Route::post('/config/usuarios/toggle-lotacao', [UsuariosController::class, 'toggleLotacao'])
                ->middleware('screen:10')
                ->name('config.usuarios.toggle_lotacao');

            Route::get('/config/grupos', [GrupoPermissaoController::class, 'index'])
                ->middleware('screen:11')
                ->name('config.grupos.index');

            
            // (Deixa preparado pro próximo passo)
            Route::get('/config/grupos/novo', [GrupoPermissaoController::class, 'create'])
                ->middleware('screen:11')
                ->name('config.grupos.create');
            
            Route::post('/config/grupos', [GrupoPermissaoController::class, 'store'])
                ->middleware('screen:11')
                ->name('config.grupos.store');
            
            Route::get('/config/grupos/{id}/editar', [GrupoPermissaoController::class, 'edit'])
                ->whereNumber('id')
                ->middleware('screen:11') 
                ->name('config.grupos.edit');
            
            Route::put('/config/grupos/{id}', [GrupoPermissaoController::class, 'update'])
                ->whereNumber('id')
                ->middleware('screen:11')
                ->name('config.grupos.update');
            
            Route::delete('/config/grupos/{id}', [GrupoPermissaoController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware('screen:11')
                ->name('config.grupos.destroy');

            Route::post('/config/grupos/{id}/permissoes/toggle', [GrupoPermissaoController::class, 'togglePermissao'])
                ->whereNumber('id')
                ->middleware('screen:11')
                ->name('config.grupos.permissoes.toggle');

                    
            /*
            |--------------------------------------------------------------------------
            | CADASTROS → CARGOS
            |--------------------------------------------------------------------------
            */

            Route::get('/cargos/cbo', [CboController::class, 'index'])
                ->middleware('screen:6')
                ->name('cargos.cbo.index');

            Route::get('/cargos/cbo/novo', [CboController::class, 'create'])
                ->middleware('screen:6')
                ->name('cargos.cbo.create');

            Route::post('/cargos/cbo', [CboController::class, 'store'])
                ->middleware('screen:6')
                ->name('cargos.cbo.store');

            Route::get('/cargos/cbo/check', [CboController::class, 'checkCodigo'])
                ->middleware('screen:6')
                ->name('cargos.cbo.check');

            Route::get('/cargos/cargos', [CargoController::class, 'index'])
                ->middleware('screen:7')
                ->name('cargos.cargos.index');

            Route::get('/cargos/cargos/novo', [CargoController::class, 'create'])
                ->middleware('screen:7')
                ->name('cargos.cargos.create');

            Route::get('/cargos/cargos/{id}/editar', [CargoController::class, 'edit'])
                ->middleware('screen:7')
                ->name('cargos.cargos.edit');

            Route::get('/cargos/setores-por-filial', [CargoController::class, 'setoresPorFilial'])
                ->middleware('screen:7')
                ->name('cargos.setores_por_filial');

            Route::get('/cargos/qlp', [HeadcountController::class, 'index'])
                ->name('cargos.headcount.index');

            Route::get('/cargos/qlp/setores-por-filiais', [HeadcountController::class, 'setoresPorFiliais'])
                ->name('cargos.headcount.setores_por_filiais');

            /*
            |--------------------------------------------------------------------------
            | BENEFÍCIOS → BOLSA DE ESTUDOS
            |--------------------------------------------------------------------------
            | Tela ID: 12
            | Slug: beneficios/bolsa
            */
            
            Route::get('/beneficios/bolsa', [BolsaEstudosController::class, 'index'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.index');

            Route::get('/beneficios/bolsa/grid', [BolsaEstudosController::class, 'grid'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.grid');
            
            Route::get('/beneficios/bolsa/novo', [BolsaEstudosController::class, 'create'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.create');
            
            Route::post('/beneficios/bolsa', [BolsaEstudosController::class, 'store'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.store');
            
            Route::get('/beneficios/bolsa/{id}/editar', [BolsaEstudosController::class, 'edit'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.edit');
            
            Route::put('/beneficios/bolsa/{id}', [BolsaEstudosController::class, 'update'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.update');
            
            Route::delete('/beneficios/bolsa/{id}', [BolsaEstudosController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.destroy');

            Route::post('/beneficios/bolsa/{id}/unidades', [BolsaEstudosController::class, 'storeUnidade'])
              ->whereNumber('id')
              ->middleware('screen:12')
              ->name('beneficios.bolsa.unidades.store');
            
            Route::delete('/beneficios/bolsa/{id}/unidades/{vinculo_id}', [BolsaEstudosController::class, 'destroyUnidade'])
              ->whereNumber('id')
              ->whereNumber('vinculo_id')
              ->middleware('screen:12')
              ->name('beneficios.bolsa.unidades.destroy');
            
            Route::post('/beneficios/bolsa/{id}/solicitantes', [BolsaEstudosController::class, 'storeSolicitante'])
              ->whereNumber('id')
              ->middleware('screen:12')
              ->name('beneficios.bolsa.solicitantes.store');
            
            Route::delete('/beneficios/bolsa/{id}/solicitantes/{solicitacao_id}', [BolsaEstudosController::class, 'destroySolicitante'])
              ->whereNumber('id')
              ->whereNumber('solicitacao_id')
              ->middleware('screen:12')
              ->name('beneficios.bolsa.solicitantes.destroy');

            Route::get('/beneficios/bolsa/colaborador-por-matricula', [BolsaEstudosController::class, 'colaboradorPorMatricula'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.colaborador.lookup');
            
            Route::get('/beneficios/bolsa/entidades/search', [BolsaEstudosController::class, 'entidadesSearch'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.entidades.search');
            
            Route::get('/beneficios/bolsa/cursos/search', [BolsaEstudosController::class, 'cursosSearch'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.cursos.search');

            Route::get('/beneficios/bolsa/{processo_id}/aprovacoes', [BolsaAprovacoesController::class, 'index'])
                ->whereNumber('processo_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.aprovacoes.index');
            
            Route::get('/beneficios/bolsa/{processo_id}/aprovacoes/{solicitacao_id}', [BolsaAprovacoesController::class, 'show'])
                ->whereNumber('processo_id')
                ->whereNumber('solicitacao_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.aprovacoes.show');
            
            Route::post('/beneficios/bolsa/{processo_id}/aprovacoes/{solicitacao_id}/aprovar', [BolsaAprovacoesController::class, 'aprovar'])
                ->whereNumber('processo_id')
                ->whereNumber('solicitacao_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.aprovacoes.aprovar');
            
            Route::post('/beneficios/bolsa/{processo_id}/aprovacoes/{solicitacao_id}/reprovar', [BolsaAprovacoesController::class, 'reprovar'])
                ->whereNumber('processo_id')
                ->whereNumber('solicitacao_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.aprovacoes.reprovar');

            Route::get('/beneficios/bolsa/{processo_id}/documentos', [BolsaDocumentosController::class, 'index'])
                ->whereNumber('processo_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos.index');
            
            Route::get('/beneficios/bolsa/{processo_id}/documentos/{doc_id}', [BolsaDocumentosController::class, 'show'])
                ->whereNumber('processo_id')
                ->whereNumber('doc_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos.show');
            
            Route::post('/beneficios/bolsa/{processo_id}/documentos/{doc_id}/aprovar', [BolsaDocumentosController::class, 'aprovar'])
                ->whereNumber('processo_id')
                ->whereNumber('doc_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos.aprovar');
            
            Route::post('/beneficios/bolsa/{processo_id}/documentos/{doc_id}/reprovar', [BolsaDocumentosController::class, 'reprovar'])
                ->whereNumber('processo_id')
                ->whereNumber('doc_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos.reprovar');
            
            Route::post('/beneficios/bolsa/{processo_id}/competencias/{competencia_id}/pagar', [BolsaDocumentosController::class, 'pagar'])
                ->whereNumber('processo_id')
                ->whereNumber('competencia_id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.competencias.pagar');

            Route::get('/beneficios/bolsa/relatorios', [BolsaRelatoriosController::class, 'index'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.relatorios.index');
            
            Route::get('/beneficios/bolsa/relatorios/export-pagamentos', [BolsaRelatoriosController::class, 'exportPagamentosExcel'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.relatorios.export_pagamentos');

            Route::get('/beneficios/bolsa/ajax/colaborador-por-matricula', [BolsaEstudosController::class, 'colaboradorPorMatricula'])
                ->middleware('screen:12')
                ->name('beneficios.bolsa.colaborador_por_matricula');

            // GRID dos documentos (AJAX)
            Route::get('/beneficios/bolsa/{id}/documentos-grid', [BolsaEstudosController::class, 'documentosGrid'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos_grid');
            
            // Adicionar unidade ao processo
            Route::post('/beneficios/bolsa/{id}/unidades', [BolsaEstudosController::class, 'addUnidade'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.unidades.store');
            
            // Adicionar solicitante ao processo
            Route::post('/beneficios/bolsa/{id}/solicitantes', [BolsaEstudosController::class, 'addSolicitante'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.solicitantes.store');
            
            // Adicionar documento ao processo (modal)
            Route::post('/beneficios/bolsa/{id}/documentos', [BolsaEstudosController::class, 'addDocumento'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.documentos.store');

            
            /*
            |--------------------------------------------------------------------------
            | BOLSA → APROVAÇÕES (pendências do ciclo)
            |--------------------------------------------------------------------------
            | Botão do index (users) chama esta rota quando pendentes_count > 0
            */
            Route::get('/beneficios/bolsa/{id}/aprovacoes', [BolsaEstudosController::class, 'aprovacoes'])
                ->whereNumber('id')
                ->middleware('screen:12')
                ->name('beneficios.bolsa.aprovacoes');


            /*
            |--------------------------------------------------------------------------
            | RECRUTAMENTO E SELEÇÃO
            |--------------------------------------------------------------------------
            */

            Route::prefix('recrutamento')->group(function () {
                Route::resource('fluxo', FluxoAprovacaoController::class);
            });

        }); // 🔒 fecha middleware auth + tenant.user

    }); // 🔒 fecha domain
