@extends('layouts.app')

@section('content')
<div class="content-wrapper">
  <div class="container-full">
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="box">
            <div class="box-body">

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
                      <i data-feather="user"></i> Geral
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-usuarios" role="tab">
                      <i data-feather="users"></i> Usuários
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-whatsapp" role="tab">
                      <i data-feather="lock"></i> WhatsApp
                    </a>
                  </li>
                </ul>

                <div class="tab-content">

                  <div class="tab-pane active" id="tab-geral" role="tabpanel">
                    <h4 class="mb-10">Configurações Gerais</h4>
                    <p class="text-muted mb-0">Central de configurações do sistema.</p>
                  </div>

                  <div class="tab-pane" id="tab-usuarios" role="tabpanel">
                    <h4 class="mb-10">Usuários</h4>
                    <p class="text-muted mb-0">Configurações relacionadas a usuários (iremos evoluir depois).</p>
                  </div>

                  <div class="tab-pane" id="tab-whatsapp" role="tabpanel">
                    <h4 class="mb-15">WhatsApp (Evolution)</h4>

                    <div class="row">
                      <div class="col-12 col-lg-6">

                        <div class="mb-10">
                          <label class="form-label">Status</label><br>
                          @php
                            $state = $empresa->wa_connection_state ?? null;
                            $connected = ($state === 'open');
                          @endphp

                          <span id="js-wa-status-badge"
                                class="badge {{ $connected ? 'badge-success' : 'badge-secondary' }}">
                            {{ $connected ? 'Conectado' : ($state ?: 'Sem conexão') }}
                          </span>
                        </div>

                        <div class="mb-10">
                          <label class="form-label">Instance ID</label>
                          <div class="form-control">
                            {{ $empresa->wa_instance_id ?: '-' }}
                          </div>
                        </div>

                        <div class="mb-10">
                          <label class="form-label">Telefone (com DDI)</label>
                          <div class="form-control">
                            {{ $empresa->wa_phone ?: '-' }}
                          </div>
                        </div>

                        @if (empty($empresa->wa_instance_id))
                          <form method="POST" action="{{ route('config.whatsapp.criar_instancia', ['sub' => request()->route('sub')]) }}">
                            @csrf

                            <div class="mb-10">
                              <label class="form-label">Telefone para pareamento (opcional)</label>
                              <input type="text"
                                     name="wa_phone"
                                     class="form-control"
                                     placeholder="Ex: 5511999999999">
                              <small class="text-muted">Se vazio, você ainda consegue conectar via QR no manager.</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                              Criar instância
                            </button>
                          </form>
                        @else
                          <form method="POST" action="{{ route('config.whatsapp.gerar_qrcode', ['sub' => request()->route('sub')]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                              Gerar / Atualizar QRCode
                            </button>
                          </form>
                        @endif

                      </div>

                      <div class="col-12 col-lg-6">
                        <label class="form-label">QRCode</label>

                        <div class="position-relative p-10" style="border:1px dashed #d9d9d9; border-radius:8px; min-height:320px;">
                          {{-- Placeholder: por enquanto guardamos "code" aqui.
                               No próximo passo, vamos mudar para receber base64 real via webhook e renderizar como <img>.
                          --}}
                          @php
                            $qr = (string) ($empresa->wa_qrcode_base64 ?? '');
                          @endphp

                          @if ($qr !== '')
                            <div id="js-wa-qr-code" class="small text-break" style="user-select: all;">
                              {{ $qr }}
                            </div>
                            <div class="text-muted mt-10">
                              *No próximo passo vamos renderizar esse conteúdo como imagem (base64) via webhook.
                            </div>
                          @else
                            <div class="text-muted">
                              Clique em <b>Gerar / Atualizar QRCode</b> para solicitar ao Evolution.
                            </div>
                          @endif

                          {{-- Overlay verde quando conectado --}}
                          <div id="js-wa-overlay"
                               class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                               style="background: rgba(40,167,69,.18); border-radius:8px; {{ $connected ? '' : 'display:none;' }}">
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

                      </div>
                    </div>
                  </div>

                </div> {{-- tab-content --}}
              </div> {{-- vtabs --}}

            </div> {{-- box-body --}}
          </div> {{-- box --}}
        </div>
      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  // Atualiza feather após render/AJAX
  if (window.feather) feather.replace();

  // Poll simples do status para ligar/desligar overlay
  const statusUrl = @json(route('config.whatsapp.status', ['sub' => request()->route('sub')]));

  async function refreshStatus() {
    try {
      const res = await fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      if (!data || !data.ok || !data.hasInstance) return;

      const state = data.state || '';
      const connected = (state === 'open');

      const badge = document.getElementById('js-wa-status-badge');
      const overlay = document.getElementById('js-wa-overlay');

      if (badge) {
        badge.classList.remove('badge-success', 'badge-secondary');
        badge.classList.add(connected ? 'badge-success' : 'badge-secondary');
        badge.textContent = connected ? 'Conectado' : (state || 'Sem conexão');
      }

      if (overlay) {
        overlay.style.display = connected ? '' : 'none';
      }
    } catch (e) {}
  }

  // a cada 10s
  setInterval(refreshStatus, 10000);
})();
</script>
@endpush
