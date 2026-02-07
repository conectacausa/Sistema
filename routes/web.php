<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;

// CARGOS
use App\Http\Controllers\Cargo\CboController;
use App\Http\Controllers\Cargo\CargoController;
use App\Http\Controllers\Cargo\HeadcountController;

// CONFIG
use App\Http\Controllers\Config\UsuariosController;
use App\Http\Controllers\Config\GrupoPermissaoController;
use App\Http\Controllers\Config\FiliaisController;
use App\Http\Controllers\Config\WhatsappIntegracoesController;
use App\Http\Controllers\Config\ConfiguracoesController;
use App\Http\Controllers\Config\FilaMensagensController; // ✅ NOVO

// WEBHOOKS
use App\Http\Controllers\Webhooks\EvolutionWebhookController;

// COLABORADORES
use App\Http\Controllers\Colaboradores\ColaboradoresController;
use App\Http\Controllers\Colaboradores\ColaboradoresImportacaoController;

// BENEFÍCIOS
use App\Http\Controllers\Beneficios\BolsaEstudosController;
use App\Http\Controllers\Beneficios\BolsaDocumentosController;
use App\Http\Controllers\Beneficios\BolsaRelatoriosController;
use App\Http\Controllers\Beneficios\BolsaAprovacoesController;

// RECRUTAMENTO
use App\Http\Controllers\Recrutamento\FluxoAprovacaoController;

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

        /*
        |--------------------------------------------------------------------------
        | WEBHOOKS (SEM AUTH)
        |--------------------------------------------------------------------------
        */
        Route::post('/webhooks/evolution', [EvolutionWebhookController::class, 'handle'])
            ->name('webhooks.evolution');

        Route::middleware(['auth', 'tenant.user'])->group(function () {

            Route::get('/dashboard', fn () => view('dashboard.index'))
                ->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | CADASTROS → COLABORADORES
            |--------------------------------------------------------------------------
            | Tela ID: 13
            | Slug: colaboradores
            */
            Route::get('/colaboradores', [ColaboradoresController::class, 'index'])
                ->middleware('screen:13')
                ->name('colaboradores.index');

            Route::get('/colaboradores/importar', [ColaboradoresImportacaoController::class, 'index'])
                ->middleware('screen:14')
                ->name('colaboradores.importar.index');

            Route::post('/colaboradores/importar', [ColaboradoresImportacaoController::class, 'store'])
                ->middleware('screen:14')
                ->name('colaboradores.importar.store');

            Route::get('/colaboradores/importar/modelo', [ColaboradoresImportacaoController::class, 'downloadModelo'])
                ->middleware('screen:14')
                ->name('colaboradores.importar.modelo');

            Route::get('/colaboradores/importar/{id}/rejeitados', [ColaboradoresImportacaoController::class, 'downloadRejeitados'])
                ->whereNumber('id')
                ->middleware('screen:14')
                ->name('colaboradores.importar.rejeitados');

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES → PÁGINA CENTRAL (/config)
            |--------------------------------------------------------------------------
            | Tela ID: 15
            */
            Route::prefix('config')
                ->middleware('screen:15')
                ->group(function () {

                    Route::get('/', [ConfiguracoesController::class, 'index'])
                        ->name('config.index');

                    // WhatsApp (Evolution)
                    Route::post('/whatsapp/criar-instancia', [ConfiguracoesController::class, 'whatsappCriarInstancia'])
                        ->name('config.whatsapp.criar_instancia');

                    Route::post('/whatsapp/request-qr', [ConfiguracoesController::class, 'whatsappRequestQr'])
                        ->name('config.whatsapp.request_qr');

                    Route::get('/whatsapp/status', [ConfiguracoesController::class, 'whatsappStatus'])
                        ->name('config.whatsapp.status');
                });

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES → FILA DE MENSAGENS
            |--------------------------------------------------------------------------
            | Tela ID: 16
            | Slug: config/fila
            */
            Route::prefix('config/fila')
                ->middleware('screen:16')
                ->group(function () {

                    Route::get('/', [FilaMensagensController::class, 'index'])
                        ->name('config.fila.index');

                    Route::post('/{id}/cancelar', [FilaMensagensController::class, 'cancelar'])
                        ->whereNumber('id')
                        ->name('config.fila.cancelar');
                });

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES → FILIAIS
            |--------------------------------------------------------------------------
            | Tela ID: 5
            */
            Route::prefix('config/filiais')
                ->middleware('screen:5')
                ->group(function () {

                    Route::get('/', [FiliaisController::class, 'index'])
                        ->name('config.filiais.index');

                    Route::get('/novo', [FiliaisController::class, 'create'])
                        ->name('config.filiais.create');

                    Route::get('/{id}/editar', [FiliaisController::class, 'edit'])
                        ->whereNumber('id')
                        ->name('config.filiais.edit');

                    Route::delete('/{id}', [FiliaisController::class, 'destroy'])
                        ->whereNumber('id')
                        ->name('config.filiais.destroy');

                    Route::get('/grid', [FiliaisController::class, 'grid'])
                        ->name('config.filiais.grid');

                    Route::get('/paises', [FiliaisController::class, 'paises'])
                        ->name('config.filiais.paises');

                    Route::get('/estados', [FiliaisController::class, 'estados'])
                        ->name('config.filiais.estados');

                    Route::get('/cidades', [FiliaisController::class, 'cidades'])
                        ->name('config.filiais.cidades');
                });

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES → USUÁRIOS
            |--------------------------------------------------------------------------
            | Tela ID: 10
            */
            Route::prefix('config/usuarios')
                ->middleware('screen:10')
                ->group(function () {

                    Route::get('/', [UsuariosController::class, 'index'])
                        ->name('config.usuarios.index');

                    Route::get('/novo', [UsuariosController::class, 'create'])
                        ->name('config.usuarios.create');

                    Route::post('/', [UsuariosController::class, 'store'])
                        ->name('config.usuarios.store');

                    Route::get('/{id}/editar', [UsuariosController::class, 'edit'])
                        ->whereNumber('id')
                        ->name('config.usuarios.edit');

                    Route::put('/{id}', [UsuariosController::class, 'update'])
                        ->whereNumber('id')
                        ->name('config.usuarios.update');

                    Route::delete('/{id}', [UsuariosController::class, 'destroy'])
                        ->whereNumber('id')
                        ->name('config.usuarios.destroy');

                    Route::post('/{id}/inativar', [UsuariosController::class, 'inativar'])
                        ->whereNumber('id')
                        ->name('config.usuarios.inativar');

                    Route::get('/setores-por-filial', [UsuariosController::class, 'setoresPorFilial'])
                        ->name('config.usuarios.setores_por_filial');

                    Route::get('/lotacoes-grid', [UsuariosController::class, 'lotacoesGrid'])
                        ->name('config.usuarios.lotacoes_grid');

                    Route::post('/toggle-lotacao', [UsuariosController::class, 'toggleLotacao'])
                        ->name('config.usuarios.toggle_lotacao');
                });

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES → GRUPOS DE PERMISSÃO
            |--------------------------------------------------------------------------
            | Tela ID: 11
            */
            Route::prefix('config/grupos')
                ->middleware('screen:11')
                ->group(function () {

                    Route::get('/', [GrupoPermissaoController::class, 'index'])
                        ->name('config.grupos.index');

                    Route::get('/novo', [GrupoPermissaoController::class, 'create'])
                        ->name('config.grupos.create');

                    Route::post('/', [GrupoPermissaoController::class, 'store'])
                        ->name('config.grupos.store');

                    Route::get('/{id}/editar', [GrupoPermissaoController::class, 'edit'])
                        ->whereNumber('id')
                        ->name('config.grupos.edit');

                    Route::put('/{id}', [GrupoPermissaoController::class, 'update'])
                        ->whereNumber('id')
                        ->name('config.grupos.update');

                    Route::delete('/{id}', [GrupoPermissaoController::class, 'destroy'])
                        ->whereNumber('id')
                        ->name('config.grupos.destroy');

                    Route::post('/{id}/permissoes/toggle', [GrupoPermissaoController::class, 'togglePermissao'])
                        ->whereNumber('id')
                        ->name('config.grupos.permissoes.toggle');
                });

            /*
            |--------------------------------------------------------------------------
            | CADASTROS → CARGOS
            |--------------------------------------------------------------------------
            | Telas:
            | 6 = CBO
            | 7 = Cargos
            */
            Route::prefix('cargos')->group(function () {

                Route::prefix('cbo')
                    ->middleware('screen:6')
                    ->group(function () {
                        Route::get('/', [CboController::class, 'index'])
                            ->name('cargos.cbo.index');

                        Route::get('/novo', [CboController::class, 'create'])
                            ->name('cargos.cbo.create');

                        Route::post('/', [CboController::class, 'store'])
                            ->name('cargos.cbo.store');

                        Route::get('/check', [CboController::class, 'checkCodigo'])
                            ->name('cargos.cbo.check');
                    });

                Route::prefix('cargos')
                    ->middleware('screen:7')
                    ->group(function () {
                        Route::get('/', [CargoController::class, 'index'])
                            ->name('cargos.cargos.index');

                        Route::get('/novo', [CargoController::class, 'create'])
                            ->name('cargos.cargos.create');

                        Route::get('/{id}/editar', [CargoController::class, 'edit'])
                            ->whereNumber('id')
                            ->name('cargos.cargos.edit');

                        Route::get('/setores-por-filial', [CargoController::class, 'setoresPorFilial'])
                            ->name('cargos.setores_por_filial');
                    });

                Route::prefix('qlp')->group(function () {
                    Route::get('/', [HeadcountController::class, 'index'])
                        ->name('cargos.headcount.index');

                    Route::get('/setores-por-filiais', [HeadcountController::class, 'setoresPorFiliais'])
                        ->name('cargos.headcount.setores_por_filiais');
                });
            });

            /*
            |--------------------------------------------------------------------------
            | BENEFÍCIOS → BOLSA DE ESTUDOS
            |--------------------------------------------------------------------------
            | Tela ID: 12
            */
            Route::prefix('beneficios/bolsa')
                ->middleware('screen:12')
                ->group(function () {

                    Route::get('/', [BolsaEstudosController::class, 'index'])
                        ->name('beneficios.bolsa.index');

                    Route::get('/grid', [BolsaEstudosController::class, 'grid'])
                        ->name('beneficios.bolsa.grid');

                    Route::get('/novo', [BolsaEstudosController::class, 'create'])
                        ->name('beneficios.bolsa.create');

                    Route::post('/', [BolsaEstudosController::class, 'store'])
                        ->name('beneficios.bolsa.store');

                    Route::get('/{id}/editar', [BolsaEstudosController::class, 'edit'])
                        ->whereNumber('id')
                        ->name('beneficios.bolsa.edit');

                    Route::put('/{id}', [BolsaEstudosController::class, 'update'])
                        ->whereNumber('id')
                        ->name('beneficios.bolsa.update');

                    Route::delete('/{id}', [BolsaEstudosController::class, 'destroy'])
                        ->whereNumber('id')
                        ->name('beneficios.bolsa.destroy');

                    Route::get('/colaborador-por-matricula', [BolsaEstudosController::class, 'colaboradorPorMatricula'])
                        ->name('beneficios.bolsa.colaborador.lookup');

                    Route::get('/entidades/search', [BolsaEstudosController::class, 'entidadesSearch'])
                        ->name('beneficios.bolsa.entidades.search');

                    Route::get('/cursos/search', [BolsaEstudosController::class, 'cursosSearch'])
                        ->name('beneficios.bolsa.cursos.search');

                    Route::get('/{processo_id}/aprovacoes', [BolsaAprovacoesController::class, 'index'])
                        ->whereNumber('processo_id')
                        ->name('beneficios.bolsa.aprovacoes.index');

                    Route::get('/{processo_id}/aprovacoes/{solicitacao_id}', [BolsaAprovacoesController::class, 'show'])
                        ->whereNumber('processo_id')
                        ->whereNumber('solicitacao_id')
                        ->name('beneficios.bolsa.aprovacoes.show');

                    Route::post('/{processo_id}/aprovacoes/{solicitacao_id}/aprovar', [BolsaAprovacoesController::class, 'aprovar'])
                        ->whereNumber('processo_id')
                        ->whereNumber('solicitacao_id')
                        ->name('beneficios.bolsa.aprovacoes.aprovar');

                    Route::post('/{processo_id}/aprovacoes/{solicitacao_id}/reprovar', [BolsaAprovacoesController::class, 'reprovar'])
                        ->whereNumber('processo_id')
                        ->whereNumber('solicitacao_id')
                        ->name('beneficios.bolsa.aprovacoes.reprovar');

                    Route::get('/{processo_id}/documentos', [BolsaDocumentosController::class, 'index'])
                        ->whereNumber('processo_id')
                        ->name('beneficios.bolsa.documentos.index');

                    Route::get('/{processo_id}/documentos/{doc_id}', [BolsaDocumentosController::class, 'show'])
                        ->whereNumber('processo_id')
                        ->whereNumber('doc_id')
                        ->name('beneficios.bolsa.documentos.show');

                    Route::post('/{processo_id}/documentos/{doc_id}/aprovar', [BolsaDocumentosController::class, 'aprovar'])
                        ->whereNumber('processo_id')
                        ->whereNumber('doc_id')
                        ->name('beneficios.bolsa.documentos.aprovar');

                    Route::post('/{processo_id}/documentos/{doc_id}/reprovar', [BolsaDocumentosController::class, 'reprovar'])
                        ->whereNumber('processo_id')
                        ->whereNumber('doc_id')
                        ->name('beneficios.bolsa.documentos.reprovar');

                    Route::get('/relatorios', [BolsaRelatoriosController::class, 'index'])
                        ->name('beneficios.bolsa.relatorios.index');
                });

            /*
            |--------------------------------------------------------------------------
            | RECRUTAMENTO
            |--------------------------------------------------------------------------
            */
            Route::prefix('recrutamento')->group(function () {
                Route::resource('fluxo', FluxoAprovacaoController::class);
            });

        }); // 🔒 fecha middleware auth + tenant.user

    }); // 🔒 fecha domain
