<?php

namespace App\Jobs;

use App\Models\Colaborador;
use App\Models\ColaboradoresImportacao;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarColaboradoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $importacaoId = null;

    // compatibilidade com jobs antigos
    public ?int $empresaId = null;
    public ?string $path = null;
    public ?int $userId = null;

    /**
     * Formatos aceitos:
     * - novo: __construct(int $importacaoId)
     * - antigo: __construct(int $empresaId, string $path, int $userId)
     */
    public function __construct(...$args)
    {
        if (count($args) === 1) {
            $this->importacaoId = (int) $args[0];
        } elseif (count($args) >= 2) {
            $this->empresaId = (int) $args[0];
            $this->path = (string) $args[1];
            $this->userId = isset($args[2]) ? (int) $args[2] : null;
        }
    }

    public function handle(): void
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $this->failImport(null, 'Dependência ausente: phpoffice/phpspreadsheet.');
            return;
        }

        // 1) tenta achar importação (novo formato) SEM scopes (worker não tem auth/request)
        $imp = null;
        if (!empty($this->importacaoId)) {
            $imp = ColaboradoresImportacao::query()
                ->withoutGlobalScopes()
                ->find((int) $this->importacaoId);
        }

        // 2) fallback para jobs antigos (empresaId + path) SEM scopes
        if (!$imp && !empty($this->empresaId) && !empty($this->path)) {
            $imp = ColaboradoresImportacao::query()
                ->withoutGlobalScopes()
                ->where('empresa_id', (int) $this->empresaId)
                ->where('arquivo_path', (string) $this->path)
                ->orderByDesc('id')
                ->first();
        }

        // com tracking
        if ($imp) {
            $empresaId = (int) $imp->empresa_id;
            $path = (string) $imp->arquivo_path;

            $imp->update([
                'status' => 'processing',
                'started_at' => now(),
                'mensagem_erro' => null,
            ]);

            // respeita root do disk local (no seu servidor: storage/app/private)
            $fullPath = Storage::disk('local')->path($path);

            if (!file_exists($fullPath)) {
                $this->failImport($imp, 'Arquivo não encontrado em disco: ' . $fullPath);
                return;
            }

            $this->processarXlsx($empresaId, $fullPath, $imp);
            return;
        }

        // sem tracking (job antigo)
        if (empty($this->empresaId) || empty($this->path)) {
            Log::warning('ImportarColaboradoresJob: payload inválido (sem tracking)', [
                'importacao_id' => $this->importacaoId,
                'empresa_id' => $this->empresaId,
                'path' => $this->path,
            ]);
            return;
        }

        $fullPath = Storage::disk('local')->path((string) $this->path);

        if (!file_exists($fullPath)) {
            Log::warning('ImportarColaboradoresJob: arquivo não encontrado (sem tracking)', [
                'empresa_id' => $this->empresaId,
                'path' => $this->path,
                'full' => $fullPath,
            ]);
            return;
        }

        $this->processarXlsx((int) $this->empresaId, $fullPath, null);
    }

    private function processarXlsx(int $empresaId, string $fullPath, ?ColaboradoresImportacao $imp): void
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);

            // evita ler "Instrucoes"
            $sheet = $spreadsheet->getSheetByName('Colaboradores') ?? $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();

            $headerRow = $sheet->rangeToArray("A1:{$highestCol}1", null, true, false);
            $headers = array_map(fn($h) => trim(mb_strtolower((string) $h)), $headerRow[0] ?? []);

            $idx = function (string $name) use ($headers) {
                $pos = array_search($name, $headers, true);
                return $pos === false ? null : $pos;
            };

            $iNome = $idx('nome');
            $iCpf  = $idx('cpf');
            $iSexo = $idx('sexo');
            $iData = $idx('data_admissao');
            $iMat  = $idx('matricula');

            if ($iNome === null || $iCpf === null) {
                if ($imp) {
                    $this->failImport($imp, 'Cabeçalho inválido. Campos obrigatórios: nome, cpf.');
                } else {
                    Log::warning('ImportarColaboradoresJob: cabeçalho inválido (sem tracking)', [
                        'empresa_id' => $empresaId,
                        'headers' => $headers,
                    ]);
                }
                return;
            }

            $totalLinhas = max(0, $highestRow - 1);
            if ($imp) {
                $imp->update(['total_linhas' => $totalLinhas]);
            }

            $importados = 0;
            $ignorados  = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}", null, true, false);
                $line = $values[0] ?? [];

                $nome = trim((string) ($line[$iNome] ?? ''));
                $cpfRaw = (string) ($line[$iCpf] ?? '');
                $cpf = preg_replace('/\D+/', '', $cpfRaw);

                if ($nome === '' || $cpf === '' || strlen($cpf) !== 11) {
                    $ignorados++;
                    continue;
                }

                $sexo = $iSexo !== null ? trim((string) ($line[$iSexo] ?? '')) : null;
                $matricula = $iMat !== null ? trim((string) ($line[$iMat] ?? '')) : null;

                $dataAdmissao = null;
                if ($iData !== null) {
                    $cell = $line[$iData] ?? null;

                    if (is_numeric($cell)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $cell);
                        $dataAdmissao = Carbon::instance($dt)->format('Y-m-d');
                    } else {
                        $txt = trim((string) $cell);
                        if ($txt !== '') {
                            try { $dataAdmissao = Carbon::parse($txt)->format('Y-m-d'); } catch (\Throwable $e) {}
                        }
                    }
                }

                // Upsert por empresa + cpf
                $colaborador = Colaborador::query()
                    ->where('empresa_id', $empresaId)
                    ->where('cpf', $cpf)
                    ->first();

                if (!$colaborador) {
                    $colaborador = new Colaborador();
                    $colaborador->empresa_id = $empresaId;
                    $colaborador->cpf = $cpf;
                }

                $colaborador->nome = $nome;

                if ($sexo) {
                    $sx = mb_strtoupper($sexo);
                    if (in_array($sx, ['M', 'F'], true)) {
                        $colaborador->sexo = $sx;
                    }
                }

                if ($matricula) $colaborador->matricula = $matricula;
                if ($dataAdmissao) $colaborador->data_admissao = $dataAdmissao;

                $colaborador->save();
                $importados++;

                if ($imp && (($importados + $ignorados) % 50 === 0)) {
                    $imp->update(['importados' => $importados, 'ignorados' => $ignorados]);
                }
            }

            if ($imp) {
                $imp->update([
                    'status' => 'done',
                    'importados' => $importados,
                    'ignorados' => $ignorados,
                    'finished_at' => now(),
                ]);
            }

        } catch (\Throwable $e) {
            if ($imp) {
                $this->failImport($imp, $e->getMessage());
            } else {
                Log::error('ImportarColaboradoresJob: erro ao processar XLSX (sem tracking)', [
                    'empresa_id' => $empresaId,
                    'file' => $fullPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function failImport(?ColaboradoresImportacao $imp, string $msg): void
    {
        if ($imp) {
            $imp->update([
                'status' => 'failed',
                'mensagem_erro' => $msg,
                'finished_at' => now(),
            ]);
            return;
        }

        Log::warning('ImportarColaboradoresJob: falha (sem tracking)', [
            'importacao_id' => $this->importacaoId,
            'empresa_id' => $this->empresaId,
            'path' => $this->path,
            'msg' => $msg,
        ]);
    }
}
