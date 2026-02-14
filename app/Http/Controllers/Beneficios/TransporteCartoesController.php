<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteCartaoSaldo;
use App\Models\Beneficios\Transporte\TransporteCartaoUso;
use App\Models\Beneficios\Transporte\TransporteVinculo;

class TransporteCartoesController extends TransporteBaseController
{
    private int $TELA_ID = 25;

    public function importarSaldos(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        // GET: tela | POST: processa (CSV simples)
        if ($request->isMethod('get')) {
            return view('beneficios.transporte.cartoes.importar_saldos', compact('sub'));
        }

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'arquivo' => 'required|file',
            'data_referencia' => 'nullable|date',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $file = $request->file('arquivo');
        $dataRef = $request->get('data_referencia');

        // CSV esperado: numero_cartao;saldo  (ou numero_cartao, saldo)
        $content = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $importados = 0;

        DB::transaction(function () use ($content, $empresaId, $dataRef, &$importados) {
            foreach ($content as $line) {
                $line = trim($line);
                if ($line === '') continue;

                // pula header
                if (stripos($line, 'numero') !== false && stripos($line, 'saldo') !== false) continue;

                $sep = (strpos($line, ';') !== false) ? ';' : ',';
                $parts = array_map('trim', explode($sep, $line));

                if (count($parts) < 2) continue;

                $numero = (string) $parts[0];
                $saldo  = (float) str_replace(',', '.', (string) $parts[1]);

                TransporteCartaoSaldo::create([
                    'empresa_id' => $empresaId,
                    'numero_cartao' => $numero,
                    'saldo' => $saldo,
                    'data_referencia' => $dataRef,
                    'origem' => 'importacao',
                ]);

                $importados++;
            }
        });

        return back()->with('success', "Importação concluída. Registros: {$importados}");
    }

    public function consulta(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $filialId = (int) $request->get('filial_id', 0);
        $numeroCartao = trim((string) $request->get('numero_cartao', ''));

        $filiais = DB::table('filiais')->where('empresa_id', $empresaId)->orderBy('nome')->get();

        $resultado = null;

        if ($filialId > 0 && $numeroCartao !== '') {
            $saldoAtual = TransporteCartaoSaldo::query()
                ->where('empresa_id', $empresaId)
                ->where('numero_cartao', $numeroCartao)
                ->orderByDesc('id')
                ->first();

            // vínculo ativo do cartão
            $vinculo = TransporteVinculo::query()
                ->where('empresa_id', $empresaId)
                ->where('numero_cartao', $numeroCartao)
                ->where('status', 'ativo')
                ->orderByDesc('id')
                ->first();

            // se quiser filtrar por filial: checa se a linha está vinculada à filial via pivot
            if ($vinculo && $filialId > 0) {
                $ok = DB::table('transporte_linha_filiais')
                    ->where('linha_id', $vinculo->linha_id)
                    ->where('filial_id', $filialId)
                    ->exists();
                if (!$ok) $vinculo = null;
            }

            $usuario = null;
            if ($vinculo) {
                $usuario = DB::table('usuarios')
                    ->where('empresa_id', $empresaId)
                    ->where('id', $vinculo->usuario_id)
                    ->first(['id','nome_completo','cpf','matricula']);
            }

            $resultado = [
                'numero_cartao' => $numeroCartao,
                'saldo' => $saldoAtual?->saldo,
                'data_referencia' => $saldoAtual?->data_referencia,
                'usuario' => $usuario,
                'vinculo' => $vinculo,
            ];
        }

        return view('beneficios.transporte.cartoes.consulta', compact('sub','filiais','filialId','numeroCartao','resultado'));
    }

    public function usos(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $numeroCartao = trim((string) $request->get('numero_cartao', ''));
        $dtIni = $request->get('dt_ini');
        $dtFim = $request->get('dt_fim');

        $usos = TransporteCartaoUso::query()
            ->where('empresa_id', $empresaId)
            ->when($numeroCartao, fn($q) => $q->where('numero_cartao', $numeroCartao))
            ->when($dtIni, fn($q) => $q->where('data_hora_uso', '>=', $dtIni . ' 00:00:00'))
            ->when($dtFim, fn($q) => $q->where('data_hora_uso', '<=', $dtFim . ' 23:59:59'))
            ->orderByDesc('data_hora_uso')
            ->paginate(30)
            ->appends($request->all());

        return view('beneficios.transporte.cartoes.usos', compact('sub','usos','numeroCartao','dtIni','dtFim'));
    }
}
