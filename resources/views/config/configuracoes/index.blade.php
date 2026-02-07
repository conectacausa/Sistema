{{-- resources/views/config/configuracoes/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content-wrapper">
  <div class="container-full">
    <section class="content">

      <div class="row">
        <div class="col-12">
          <div class="box">
            <div class="box-body">

              {{-- Alerts --}}
              @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
              @endif
              @if (session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
              @endif

              <div class="vtabs">
                <ul class="nav nav-tabs tabs-vertical" role="tablist">

                  <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-geral" role="tab">
                      <i data-feather="user"></i>
                      Geral
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-usuarios" role="tab">
                      <i data-feather="users"></i>
                      Usuários
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-whatsapp" role="tab" id="tab-whatsapp-link">
                      <i data-feather="lock"></i>
                      WhatsApp
                    </a>
                  </li>

                </ul>

                <div class="tab-content">

                  {{-- TAB: GERAL --}}
                  <div class="tab-pane active" id="tab-geral" role="tabpanel">
                    <h4 class="mb-10">Configurações</h4>
                    <p class="text-muted mb-0">Central de configurações do sistema.</p>
                  </div>

                  {{-- TAB: USUÁRIOS --}}
                  <div class="tab-pane" id="tab-usuarios" role="tabpanel">
                    <h4 class="mb-10">Usuários</h4>
                    <p class="text-muted mb-0">Configurações relacionadas a usuários (evoluiremos depois).</p>
                  </div>

                  {{-- TAB: WHATSAPP --}}
                  <div class="tab-pane" id="tab-whatsapp" role="tabpanel">
                    <h4 class="mb-15">WhatsApp (Evolution)</h4>

                    @php
                      $sub = request()->route('sub');
                      $hasInstance = !empty($empresa->wa_instance_id) && !empty($empresa->wa_instance_name);

                      // Se tem QR salvo, então NÃO está conectado (regra rígida)
                      $qrCode = (string) ($empresa->wa_qrcode_base64 ?? '');
                      $state  = (string) ($empresa->wa_connection_state ?? '');
                      $connected = ($state === 'open') && ($qrCode === '');
                    @endphp

                    <div class="row">
                      <div class="col-12 col-lg-6">

                        <div class="mb-10">
                          <label class="form-label">Status</label><br>
                          <span id="js-wa-status-badge"
                                class="badge {{ $connected ? 'badge-success' : 'badge-secondary' }}">
                            {{ $connected ? 'Conectado' : ($qrCode !== '' ? 'Aguardando QR' : ($state ?: 'Sem conexão')) }}
                          </span>
                        </div>

                        <div class="mb-10">
                          <label class="form-label">Instance ID</label>
                          <div class="form-control">
                            {{ $empresa->wa_instance_id ?: '-' }}
                          </div>
                        </div>

                        <div class="mb-10">
                          <label class="form-label">Telefone</label>
                          <div class="form-control">
                            {{ $empresa->wa_phone ?: '-' }}
                          </div>
                        </div>

                        @if (!$hasInstance)
                          <form method="POST" action="{{ route('config.whatsapp.criar_instancia', ['sub' => $sub]) }}">
                            @csrf

                            <div class="mb-10">
                              <label class="form-label">Telefone para pareamento (opcional, com DDI)</label>
                              <input type="text"
                                     name="wa_phone"
                                     class="form-control"
                                     placeholder="Ex: 5511999999999">
                              <small class="text-muted">
                                Se vazio, você ainda consegue conectar via QR no manager.
                              </small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                              Criar instância
                            </button>
                          </form>
                        @else
                          <form method="POST" action="{{ route('config.whatsapp.gerar_qrcode', ['sub' => $sub]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                              Gerar / Atualizar QRCode
                            </button>
                          </form>

                          <button type="button" class="btn btn-outline-secondary ms-5" id="btn-refresh-status">
                            Atualizar status
                          </button>
                        @endif

                        <div class="mt-15 text-muted small">
                          <b>Obs:</b> A base do Evolution é fixa no servidor. O token/ApiKey da instância é gerado automaticamente e salvo no banco (não exibido).
                        </div>
                      </div>

                      <div class="col-12 col-lg-6">
                        <label class="form-label">QRCode</label>

                        <div class="position-relative p-10"
                             style="border:1px dashed #d9d9d9; border-radius:8px; min-height:320px;">

                          <div id="js-qr-wrap" class="d-flex align-items-center justify-content-center" style="min-height:280px;">
                            <div id="js-qr"></div>

                            <div id="js-qr-empty" class="text-muted" style="{{ $qrCode === '' ? '' : 'display:none;' }}">
                              Clique em <b>Gerar / Atualizar QRCode</b> para solicitar ao Evolution.
                            </div>
                          </div>

                          {{-- Overlay verde: SEMPRE oculto no HTML. Só aparece quando o /status retornar connected=true --}}
                          <div id="js-wa-overlay"
                               class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center d-none"
                               style="background: rgba(40,167,69,.18); border-radius:8px; display:none;">
                            <div class="text-center">
                              <div class="mb-5">
                                <span class="badge badge-success" style="font-size:14px; padding:8px 12px;">
                                  ✓ Conectado
                                </span>
                              </div>
                              <div class="text-success">WhatsApp conectado com sucesso.</div>
                            </div>
                          </div>

                        </div>

                        <div class="mt-10 text-muted small">
                          Se o QR não aparecer, clique em <b>Gerar / Atualizar QRCode</b> novamente.
                        </div>
                      </div>
                    </div>

                  </div>{{-- tab whatsapp --}}

                </div>{{-- tab-content --}}
              </div>{{-- vtabs --}}

            </div>{{-- box-body --}}
          </div>{{-- box --}}
        </div>
      </div>

    </section>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
  function renderQr(code) {
    const el = document.getElementById('js-qr');
    const empty = document.getElementById('js-qr-empty');
    if (!el) return;

    el.innerHTML = '';
    if (!code) {
      if (empty) empty.style.display = '';
      return;
    }
    if (empty) empty.style.display = 'none';

    new QRCode(el, { text: code, width: 240, height: 240 });
  }

  // Render inicial (do banco)
  renderQr(@json($qrCode));

  if (window.feather) feather.replace();

  const statusUrl   = @json(route('config.whatsapp.status', ['sub' => $sub]));
  const btnRefresh  = document.getElementById('btn-refresh-status');
  const tabWhatsapp = document.getElementById('tab-whatsapp-link');

  async function refreshStatus() {
    try {
      const res = await fetch(statusUrl, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await res.json();
      if (!data || !data.ok || !data.hasInstance) return;

      const connected = !!data.connected;
      const state     = data.state || '';
      const needsQr   = !!data.needsQr;

      const badge   = document.getElementById('js-wa-status-badge');
      const overlay = document.getElementById('js-wa-overlay');

      // ✅ Sempre forçar o overlay a respeitar o retorno do backend
      if (overlay) {
        if (connected) {
          overlay.classList.remove('d-none');
          overlay.style.display = '';
        } else {
          overlay.classList.add('d-none');
          overlay.style.display = 'none';
        }
      }

      if (badge) {
        badge.classList.remove('badge-success', 'badge-secondary');
        badge.classList.add(connected ? 'badge-success' : 'badge-secondary');
        badge.textContent = connected
          ? 'Conectado'
          : (needsQr ? 'Aguardando QR' : (state || 'Sem conexão'));
      }

      // Se precisa de QR, renderiza/atualiza o QRCode
      if (!connected && needsQr && data.qrCode) {
        renderQr(data.qrCode);
      }

      if (window.feather) feather.replace();
    } catch (e) {
      // silencioso
    }
  }

  // Poll a cada 10s
  setInterval(refreshStatus, 10000);

  if (btnRefresh) {
    btnRefresh.addEventListener('click', function () {
      refreshStatus();
    });
  }

  // Quando clicar/abrir a tab WhatsApp, atualiza na hora
  if (tabWhatsapp) {
    tabWhatsapp.addEventListener('shown.bs.tab', function () {
      refreshStatus();
    });
  }

  // Atualiza imediatamente ao carregar
  refreshStatus();
})();
</script>
@endpush
