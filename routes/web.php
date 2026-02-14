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
use App\Http\Controllers\Config\FilaMensagensController;

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

// BENEFÍCIOS → TRANSPORTE
use App\Http\Controllers\Beneficios\TransporteLinhasController;
use App\Http\Controllers\Beneficios\TransporteMotoristasController;
use App\Http\Controllers\Beneficios\TransporteVeiculosController;
use App\Http\Controllers\Beneficios\TransporteInspecoesController;
use App\Http\Controllers\Beneficios\TransporteCartoesController;
use App\Http\Controllers\Beneficios\TransporteRelatoriosController;
use App\Http\Controllers\Beneficios\TransporteTicketsController;

// RECRUTAMENTO
use App\Http\Controllers\Recrutamento\FluxoAprovacaoController;

// ✅ AVD (Avaliação de Desempenho) — PADRÃO: pasta/namespace AVD
use App\Http\Controllers\AVD\CiclosController;
use App\Http\Controllers\AVD\PendenciasController;
use App\Http\Controllers\AVD\AvaliacaoPublicaController;
use App\Http\Controllers\AVD\ResultadosController;

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

        /*
        |--------------------------------------------------------------------------
        | AVD (PÚBLICO POR TOKEN) - SEM AUTH (mas dentro do tenant/subdomínio)
        |--------------------------------------------------------------------------
        | URL pública segura:
        | /avaliacao/{token}
        */
        Route::get('/avaliacao/{token}', [AvaliacaoPublicaController::class, 'show'])
            ->name('avd.public.show');

        Route::post('/avaliacao/{token}', [AvaliacaoPublicaController::class, 'submit'])
            ->name('avd.public.submit');

        Route::middleware(['auth', 'tenant.user'])->group(function () {

            Route::get('/dashboard', fn() => view('dashboard.index'))
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
            | AVD → AVALIAÇÃO DE DESEMPENHO
            |--------------------------------------------------------------------------
            | Telas:
            | 17 = Ciclos (listagem + cadastro/edição)
            | 18 = Pendências do usuário (Minhas Avaliações)
            | 20 = Resultados
            */
            Route::prefix('avd')->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Tela 17 — Ciclos (listagem + cadastro/edição)
                |--------------------------------------------------------------------------
                | Slug: avd/desempenho
                */
                Route::prefix('desempenho')
                    ->middleware('screen:17')
                    ->group(function () {

                        // Listagem + grid
                        Route::get('/', [CiclosController::class, 'index'])
                            ->name('avd.ciclos.index');

                        Route::get('/grid', [CiclosController::class, 'grid'])
                            ->name('avd.ciclos.grid');

                        // Criar / Salvar
                        Route::get('/criar', [CiclosController::class, 'create'])
                            ->name('avd.ciclos.create');

                        Route::post('/', [CiclosController::class, 'store'])
                            ->name('avd.ciclos.store');

                        // Editar / Atualizar
                        Route::get('/{id}/editar', [CiclosController::class, 'edit'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.edit');

                        Route::put('/{id}', [CiclosController::class, 'update'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.update');

                        // Excluir (soft delete)
                        Route::delete('/{id}', [CiclosController::class, 'destroy'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.destroy');

                        // Iniciar / Encerrar manualmente
                        Route::post('/{id}/iniciar', [CiclosController::class, 'iniciar'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.iniciar');

                        Route::post('/{id}/encerrar', [CiclosController::class, 'encerrar'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.encerrar');

                        /*
                        |--------------------------------------------------------------------------
                        | AJAX: TAB UNIDADES
                        |--------------------------------------------------------------------------
                        | Vincular:
                        | - modo=uma + filial_id
                        | - modo=todas
                        | Desvincular: usa filial_id no path
                        */
                        Route::post('/{id}/unidades/vincular', [CiclosController::class, 'unidadesVincular'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.unidades.vincular');

                        Route::delete('/{id}/unidades/{filial_id}', [CiclosController::class, 'unidadesDesvincular'])
                            ->whereNumber('id')
                            ->whereNumber('filial_id')
                            ->name('avd.ciclos.unidades.desvincular');

                        /*
                        |--------------------------------------------------------------------------
                        | AJAX: TAB COLABORADORES (PARTICIPANTES)
                        |--------------------------------------------------------------------------
                        | Vincular:
                        | - modo=individual + colaborador_id
                        | - modo=lote_filial + filial_id
                        */
                        Route::post('/{id}/participantes/vincular', [CiclosController::class, 'participantesVincular'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.participantes.vincular');

                        Route::put('/{id}/participantes/{pid}/whatsapp', [CiclosController::class, 'participantesAtualizarWhatsapp'])
                            ->whereNumber('id')
                            ->whereNumber('pid')
                            ->name('avd.ciclos.participantes.whatsapp');

                        Route::delete('/{id}/participantes/{pid}', [CiclosController::class, 'participantesRemover'])
                            ->whereNumber('id')
                            ->whereNumber('pid')
                            ->name('avd.ciclos.participantes.remover');

                        /*
                        |--------------------------------------------------------------------------
                        | AJAX: TAB PILARES
                        |--------------------------------------------------------------------------
                        */
                        Route::post('/{id}/pilares/salvar', [CiclosController::class, 'pilaresSalvar'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.pilares.salvar');

                        Route::delete('/{id}/pilares/{pilar_id}', [CiclosController::class, 'pilaresExcluir'])
                            ->whereNumber('id')
                            ->whereNumber('pilar_id')
                            ->name('avd.ciclos.pilares.excluir');

                        /*
                        |--------------------------------------------------------------------------
                        | AJAX: TAB PERGUNTAS
                        |--------------------------------------------------------------------------
                        */
                        Route::post('/{id}/perguntas/salvar', [CiclosController::class, 'perguntasSalvar'])
                            ->whereNumber('id')
                            ->name('avd.ciclos.perguntas.salvar');

                        Route::delete('/{id}/perguntas/{pergunta_id}', [CiclosController::class, 'perguntasExcluir'])
                            ->whereNumber('id')
                            ->whereNumber('pergunta_id')
                            ->name('avd.ciclos.perguntas.excluir');
                    });

                /*
                |--------------------------------------------------------------------------
                | Tela 18 — Pendências do usuário (Minhas Avaliações)
                |--------------------------------------------------------------------------
                | Slug: avd/gestor
                */
                Route::get('/gestor', [PendenciasController::class, 'index'])
                    ->middleware('screen:18')
                    ->name('avd.pendencias.index');

                /*
                |--------------------------------------------------------------------------
                | Tela 20 — Resultados
                |--------------------------------------------------------------------------
                | Slug: avd/resultados
                */
                Route::get('/resultados', [ResultadosController::class, 'index'])
                    ->middleware('screen:20')
                    ->name('avd.resultados.index');
            });

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
            | BENEFÍCIOS → TRANSPORTE
            |--------------------------------------------------------------------------
            | Telas:
            | 21 = Linhas (inclui Operação)
            | 22 = Motoristas
            | 23 = Veículos
            | 24 = Inspeções
            | 25 = Importar Saldos + Usos do Cartão
            | 26 = Consulta Cartão
            | 27 = Relatórios + Exportação folha + Importar custos + Tickets
            */
            Route::prefix('beneficios/transporte')->group(function () {

                /*
                |----------------------------------------------------------------------
                | LINHAS (Tela 21)
                |----------------------------------------------------------------------
                | Slug: beneficios/transporte/linhas
                */
                Route::prefix('linhas')
                    ->middleware('screen:21')
                    ->group(function () {

                        Route::get('/', [TransporteLinhasController::class, 'index'])
                            ->name('beneficios.transporte.linhas.index');

                        Route::get('/novo', [TransporteLinhasController::class, 'create'])
                            ->name('beneficios.transporte.linhas.create');

                        Route::post('/', [TransporteLinhasController::class, 'store'])
                            ->name('beneficios.transporte.linhas.store');

                        Route::get('/{id}/editar', [TransporteLinhasController::class, 'edit'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.linhas.edit');

                        Route::put('/{id}', [TransporteLinhasController::class, 'update'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.linhas.update');

                        Route::delete('/{id}', [TransporteLinhasController::class, 'destroy'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.linhas.destroy');

                        // Operação (tabs: paradas, vínculos, encerrar)
                        Route::get('/{id}/operacao', [TransporteLinhasController::class, 'operacao'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.linhas.operacao');

                        // Paradas (CRUD mínimo dentro da operação)
                        Route::post('/{linhaId}/paradas', [TransporteLinhasController::class, 'paradaStore'])
                            ->whereNumber('linhaId')
                            ->name('beneficios.transporte.linhas.parada.store');

                        Route::put('/{linhaId}/paradas/{paradaId}', [TransporteLinhasController::class, 'paradaUpdate'])
                            ->whereNumber('linhaId')
                            ->whereNumber('paradaId')
                            ->name('beneficios.transporte.linhas.parada.update');

                        Route::delete('/{linhaId}/paradas/{paradaId}', [TransporteLinhasController::class, 'paradaDestroy'])
                            ->whereNumber('linhaId')
                            ->whereNumber('paradaId')
                            ->name('beneficios.transporte.linhas.parada.destroy');

                        // Vínculos (dentro da operação)
                        Route::post('/{linhaId}/vinculos', [TransporteLinhasController::class, 'vinculoStore'])
                            ->whereNumber('linhaId')
                            ->name('beneficios.transporte.linhas.vinculo.store');

                        Route::put('/{linhaId}/vinculos/{vinculoId}/encerrar', [TransporteLinhasController::class, 'vinculoEncerrar'])
                            ->whereNumber('linhaId')
                            ->whereNumber('vinculoId')
                            ->name('beneficios.transporte.linhas.vinculo.encerrar');
                    });

                /*
                |----------------------------------------------------------------------
                | IMPORTAR CUSTOS (Tela 27) - view em /linhas/importar_custos.blade.php
                |----------------------------------------------------------------------
                | Slug: beneficios/transporte/linhas/importar-custos (ID 27)
                */
                Route::prefix('linhas')
                    ->middleware('screen:27')
                    ->group(function () {

                        Route::get('/importar-custos', [TransporteLinhasController::class, 'importarCustosForm'])
                            ->name('beneficios.transporte.linhas.importar_custos.form');

                        Route::post('/importar-custos', [TransporteLinhasController::class, 'importarCustos'])
                            ->name('beneficios.transporte.linhas.importar_custos');
                    });

                /*
                |----------------------------------------------------------------------
                | MOTORISTAS (Tela 22)
                |----------------------------------------------------------------------
                | Slug: beneficios/transporte/motoristas
                */
                Route::prefix('motoristas')
                    ->middleware('screen:22')
                    ->group(function () {

                        Route::get('/', [TransporteMotoristasController::class, 'index'])
                            ->name('beneficios.transporte.motoristas.index');

                        Route::get('/novo', [TransporteMotoristasController::class, 'create'])
                            ->name('beneficios.transporte.motoristas.create');

                        Route::post('/', [TransporteMotoristasController::class, 'store'])
                            ->name('beneficios.transporte.motoristas.store');

                        Route::get('/{id}/editar', [TransporteMotoristasController::class, 'edit'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.motoristas.edit');

                        Route::put('/{id}', [TransporteMotoristasController::class, 'update'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.motoristas.update');

                        Route::delete('/{id}', [TransporteMotoristasController::class, 'destroy'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.motoristas.destroy');
                    });

                /*
                |----------------------------------------------------------------------
                | VEÍCULOS (Tela 23)
                |----------------------------------------------------------------------
                | Slug: beneficios/transporte/veiculos
                */
                Route::prefix('veiculos')
                    ->middleware('screen:23')
                    ->group(function () {

                        Route::get('/', [TransporteVeiculosController::class, 'index'])
                            ->name('beneficios.transporte.veiculos.index');

                        Route::get('/novo', [TransporteVeiculosController::class, 'create'])
                            ->name('beneficios.transporte.veiculos.create');

                        Route::post('/', [TransporteVeiculosController::class, 'store'])
                            ->name('beneficios.transporte.veiculos.store');

                        Route::get('/{id}/editar', [TransporteVeiculosController::class, 'edit'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.veiculos.edit');

                        Route::put('/{id}', [TransporteVeiculosController::class, 'update'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.veiculos.update');

                        Route::delete('/{id}', [TransporteVeiculosController::class, 'destroy'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.veiculos.destroy');
                    });

                /*
                |----------------------------------------------------------------------
                | INSPEÇÕES (Tela 24)
                |----------------------------------------------------------------------
                | Slug: beneficios/transporte/inspecoes
                */
                Route::prefix('inspecoes')
                    ->middleware('screen:24')
                    ->group(function () {

                        Route::get('/', [TransporteInspecoesController::class, 'index'])
                            ->name('beneficios.transporte.inspecoes.index');

                        Route::get('/novo', [TransporteInspecoesController::class, 'create'])
                            ->name('beneficios.transporte.inspecoes.create');

                        Route::post('/', [TransporteInspecoesController::class, 'store'])
                            ->name('beneficios.transporte.inspecoes.store');

                        Route::get('/{id}', [TransporteInspecoesController::class, 'show'])
                            ->whereNumber('id')
                            ->name('beneficios.transporte.inspecoes.show');
                    });

                /*
                |----------------------------------------------------------------------
                | CARTÕES (IDs 25 e 26)
                |----------------------------------------------------------------------
                */
                Route::prefix('cartoes')->group(function () {

                    /*
                    |------------------------------------------------------------------
                    | Importar Saldos (Tela 25)
                    |------------------------------------------------------------------
                    | Slug: beneficios/transporte/cartoes/importar-saldos
                    */
                    Route::get('/importar-saldos', [TransporteCartoesController::class, 'importarSaldosForm'])
                        ->middleware('screen:25')
                        ->name('beneficios.transporte.cartoes.importar_saldos.form');

                    Route::post('/importar-saldos', [TransporteCartoesController::class, 'importarSaldos'])
                        ->middleware('screen:25')
                        ->name('beneficios.transporte.cartoes.importar_saldos');

                    /*
                    |------------------------------------------------------------------
                    | Usos do Cartão (Tela 25 - mesmo ID)
                    |------------------------------------------------------------------
                    | Slug: beneficios/transporte/cartoes/usos
                    */
                    Route::get('/usos', [TransporteCartoesController::class, 'usos'])
                        ->middleware('screen:25')
                        ->name('beneficios.transporte.cartoes.usos');

                    /*
                    |------------------------------------------------------------------
                    | Consulta Cartão (Tela 26)
                    |------------------------------------------------------------------
                    | Slug: beneficios/transporte/cartoes/consulta
                    */
                    Route::get('/consulta', [TransporteCartoesController::class, 'consulta'])
                        ->middleware('screen:26')
                        ->name('beneficios.transporte.cartoes.consulta');
                });

                /*
                |----------------------------------------------------------------------
                | RELATÓRIOS (Tela 27)
                |----------------------------------------------------------------------
                | Slugs:
                | - beneficios/transporte/relatorios/recarga (ID 27)
                | - beneficios/transporte/relatorios/exportacao-folha (ID 27)
                */
                Route::prefix('relatorios')
                    ->middleware('screen:27')
                    ->group(function () {

                        Route::get('/recarga', [TransporteRelatoriosController::class, 'recarga'])
                            ->name('beneficios.transporte.relatorios.recarga');

                        Route::get('/exportacao-folha', [TransporteRelatoriosController::class, 'exportacaoFolha'])
                            ->name('beneficios.transporte.relatorios.exportacao_folha');
                    });

                /*
                |----------------------------------------------------------------------
                | TICKETS (Tela 27)
                |----------------------------------------------------------------------
                | Slugs:
                | - beneficios/transporte/tickets/blocos (ID 27)
                | - beneficios/transporte/tickets/entregas (ID 27)
                */
                Route::prefix('tickets')
                    ->middleware('screen:27')
                    ->group(function () {

                        Route::get('/blocos', [TransporteTicketsController::class, 'blocos'])
                            ->name('beneficios.transporte.tickets.blocos');

                        Route::post('/blocos', [TransporteTicketsController::class, 'blocosStore'])
                            ->name('beneficios.transporte.tickets.blocos.store');

                        Route::get('/entregas', [TransporteTicketsController::class, 'entregas'])
                            ->name('beneficios.transporte.tickets.entregas');

                        Route::post('/entregas', [TransporteTicketsController::class, 'entregasStore'])
                            ->name('beneficios.transporte.tickets.entregas.store');
                    });
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
