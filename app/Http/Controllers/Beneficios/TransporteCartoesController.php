<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteCartoesController extends Controller
{
    private const T_SALDOS = 'transporte_cartoes_saldos';
    private const T_USOS   = 'transporte_cartoes_usos';
    private const T_VINCULOS = 'transporte_vinculos';
    private const T_COLABS = 'colaboradores';
    private const T_FILIAIS = 'filiais';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function now()
    {
        return now();
    }

    public function importarSaldosForm(Request $request, string $sub)
    {
        return view('beneficios.transporte.cartoes.importar_saldos', compact('sub'));
    }

    public function importarSaldos(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        // Aceita CSV simples via textarea ou upload (mantém simples e funcional)
        $v = Validator::make($request->all(), [
            'linhas' => 'nullable|string',
            'arquivo' => 'nullable|file',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $rows = [];

        // 1) textarea "linhas" (cada linha: cartao;saldo)
        $raw = trim((string) $request->get('linhas', ''));
        if ($raw !== '') {
            foreach (preg_split("/\r\n|\n|\r/", $raw) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = preg_split('/[;,]/', $line);
                $cartao = trim((string) ($parts[0] ?? ''));
                $saldo  = trim((string) ($parts[1] ?? ''));
                if ($cartao !== '' && is_numeric(str_replace(',', '.', $saldo))) {
                    $rows[] = [$cartao, (float) str_replace(',', '.', $saldo)];
                }
            }
        }

        // 2) arquivo (csv)
        if ($request->hasFile('arquivo')) {
            $content = (string) file_get_contents($request->file('arquivo')->getRealPath());
            foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = str_getcsv($line, ';');
                if (count($parts) < 2) $parts = str_getcsv($line, ',');
                $cartao = trim((string) ($parts[0] ?? ''));
                $saldo  = trim((string) ($parts[1] ?? ''));
                if ($cartao !== '' && is_numeric(str_replace(',', '.', $saldo))) {
                    $rows[] = [$cartao, (float) str_replace(',', '.', $saldo)];
                }
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'Nenhum dado válido para importar.');
        }

        DB::transaction(function () use ($empresaId, $rows) {
            foreach ($rows as [$cartao, $saldo]) {
                // upsert por (empresa_id, cartao_numero)
                $exists = DB::table(self::T_SALDOS)
                    ->where('empresa_id', $empresaId)
                    ->where('cartao_numero', $cartao)
                    ->exists();

                if ($exists) {
                    DB::table(self::T_SALDOS)
                        ->where('empresa_id', $empresaId)
                        ->where('cartao_numero', $cartao)
                        ->update([
                            'saldo'      => $saldo,
                            'updated_at' => $this->now(),
                        ]);
                } else {
                    DB::table(self::T_SALDOS)->insert([
                        'empresa_id'    => $empresaId,
                        'cartao_numero' => $cartao,
                        'saldo'         => $saldo,
                        'created_at'    => $this->now(),
                        'updated_at'    => $this->now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Saldos importados com sucesso.');
    }

    public function usos(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $cartao = trim((string) $request->get('cartao', ''));
        $dataIni = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $usos = DB::table(self::T_USOS)
            ->where('empresa_id', $empresaId)
            ->when($cartao !== '', fn($q) => $q->where('cartao_numero', $cartao))
            ->when($dataIni, fn($q) => $q->whereDate('data_hora', '>=', $dataIni))
            ->when($dataFim, fn($q) => $q->whereDate('data_hora', '<=', $dataFim))
            ->orderBy('data_hora', 'desc')
            ->paginate(30)
            ->withQueryString();

        return view('beneficios.transporte.cartoes.usos', compact('sub', 'usos', 'cartao', 'dataIni', 'dataFim'));
    }

    public function consulta(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $filialId = (int) $request->get('filial_id', 0);
        $cartao   = trim((string) $request->get('cartao', ''));

        $filiais = DB::table(self::T_FILIAIS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $result = null;

        if ($cartao !== '') {
            // Quem está vinculado + saldo
            $saldo = DB::table(self::T_SALDOS)
                ->where('empresa_id', $empresaId)
                ->where('cartao_numero', $cartao)
                ->value('saldo');

            $vinculo = DB::table(self::T_VINCULOS . ' as tv')
                ->leftJoin(self::T_COLABS . ' as c', 'c.id', '=', 'tv.colaborador_id')
                ->select('tv.*', 'c.nome_completo', 'c.matricula', 'c.cpf')
                ->where('tv.cartao_numero', $cartao)
                ->whereNull('tv.deleted_at')
                ->when($filialId > 0, function ($q) use ($filialId) {
                    // se você gravou filial no vínculo, ajuste aqui
                    if (Schema::hasColumn('transporte_vinculos', 'filial_id')) {
                        $q->where('tv.filial_id', $filialId);
                    }
                })
                ->orderBy('tv.id', 'desc')
                ->first();

            $result = [
                'cartao' => $cartao,
                'saldo'  => $saldo,
                'vinculo'=> $vinculo,
            ];
        }

        return view('beneficios.transporte.cartoes.consulta', compact('sub', 'filiais', 'filialId', 'cartao', 'result'));
    }
}
