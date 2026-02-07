@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
<section class="content">

  <div class="row">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between mb-10">
        <h3 class="m-0">Configurações</h3>
      </div>
    </div>
  </div>

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

          @php
            $sub = request()->route('sub');

            $hasInstance = !empty($empresa->wa_instance_id) && !empty($empresa->wa_instance_name);

            $qrRaw = (string)($empresa->wa_qrcode_base64 ?? '');
            $state = (string)($empresa->wa_connection_state ?? '');

            $connected = ($state === 'open') && ($qrRaw === '');

            $isDataUri   = str_starts_with($qrRaw, 'data:image');
            $looksBase64 = (!$isDataUri && $qrRaw !== '' && preg_match('/^[A-Za-z0-9+\/=]+$/', $qrRaw));
            $qrImgSrc    = $isDataUri ? $qrRaw : ($looksBase64 ? ('data:image/png;base64,' . $qrRaw) : '');
          @endphp

          <div class="vtabs">
            <ul class="nav nav-tabs tabs-vertical" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-whatsapp" role="tab" id="tab-whatsapp-link">
                  <i data-feather="message-circle"></i>
                  WhatsApp
                </a>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="tab-whatsapp" role="tabpanel">

                <div class="row">

                  {{-- ESQUERDA: CAMPOS / AÇÕES (MAIS LARGO E SEM TRUNCAR) --}}
                  <div class="col-12 col-lg-5">

                    <div class="mb-10">
                      <label class="form-label">Status</label><br>
                      <span id="js-wa-status-badge" class="badge {{ $connected ? 'badge-success' : 'badge-secondary' }}">
                        {{ $connected ? 'Conectado' : ($qrRaw !== '' ? 'Aguardando QR' : ($state ?: 'Sem conexão')) }}
                      </span>
                    </div>

                    <div class="mb-10">
                      <label class="form-label">Instance ID</label>
                      <input type="text" class="form-control" value="{{ $empresa->wa_instance_id ?: '-' }}" readonly>
                    </div>

                    <div class="mb-10">
                      <label class="form-label">Telefone</label>
                      <input type="text" class="form-control" value="{{ $empresa->wa_phone ?: '' }}" readonly>
                    </div>

                    @if (!$hasInstance)
                      <form method="POST" action="{{ route('config.whatsapp.criar_instancia', ['sub' => $sub]) }}">
                        @csrf

                        <div class="mb-10">
                          <label class="form-label">Telefone para pareamento (opcional)</label>
                          <input type="text"
                                 name="wa_phone"
                                 id="wa_phone"
                                 class="form-control"
                                 placeholder="(11) 99999-9999">
                        </div>

                        <button type="submit" class="btn btn-primary">
                          Criar instância
                        </button>
                      </form>
                    @else
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" id="btn-request-qr">
                          Solicitar QRCode
                        </button>

                        <button type="button" class="btn btn-outline-secondary" id="btn-refresh-status">
                          Atualizar status
                        </button>
                      </div>
                    @endif

                  </div>

                  {{-- DIREITA: QRCode (MAIOR) --}}
                  <div class="col-12 col-lg-7">
                    <label class="form-label">QRCode</label>

                    <div class="position-relative p-10"
                         style="border:1px dashed #d9d9d9; border-radius:8px; height:460px;">

                      <div class="h-100 d-flex align-items-center justify-content-center">
                        <img id="js-qr-img"
                             src="{{ $qrImgSrc }}"
                             alt="QRCode"
                             style="{{ $qrImgSrc ? '' : 'display:none;' }} max-width:360px; max-height:360px;">

                        <div id="js-qr-empty" class="text-muted text-center" style="{{ $qrImgSrc ? 'display:none;' : '' }}">
                          Nenhum QRCode disponível no momento.
                        </div>
                      </div>

                      {{-- Overlay cobrindo tudo --}}
                      <div id="js-wa-overlay"
                           class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center d-none"
                           style="background: rgba(40,167,69,.22); border-radius:8px; display:none;">
                        <div class="text-center">
                          <div class="mb-8">
                            <span class="badge badge-success" style="font-size:16px; padding:10px 14px;">
                              ✓ Conectado
                            </span>
                          </div>
                          <div class="text-success fw-600">WhatsApp conectado</div>
                        </div>
                      </div>

                    </div>
                  </div>

                </div>

              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<script>
(function () {
  if (window.feather) feather.replace();

  const statusUrl    = @json(route('config.whatsapp.status', ['sub' => $sub]));
  const requestQrUrl = @json(route('config.whatsapp.request_qr', ['sub' => $sub]));

  const btnRefresh   = document.getElementById('btn-refresh-status');
  const btnRequestQr = document.getElementById('btn-request-qr');

  // máscara telefone (somente no campo de criação)
  const phoneInput = document.getElementById('wa_phone');
  if (phoneInput) {
    if (window.Inputmask) {
      Inputmask({ mask: '(99) 99999-9999', showMaskOnHover: false }).mask(phoneInput);
    } else {
      phoneInput.addEventListener('input', function () {
        const digits = (this.value || '').replace(/\D/g,'').slice(0,11);
        let out = '';
        if (digits.length >= 1) out += '(' + digits.slice(0,2);
        if (digits.length >= 3) out += ') ' + digits.slice(2,7);
        if (digits.length >= 8) out += '-' + digits.slice(7,11);
        this.value = out;
      });
    }
  }

  function setOverlay(connected) {
    const overlay = document.getElementById('js-wa-overlay');
    if (!overlay) return;

    if (connected) {
      overlay.classList.remove('d-none');
      overlay.style.display = '';
    } else {
      overlay.classList.add('d-none');
      overlay.style.display = 'none';
    }
  }

  function setBadge(text, connected) {
    const badge = document.getElementById('js-wa-status-badge');
    if (!badge) return;

    badge.classList.remove('badge-success', 'badge-secondary');
    badge.classList.add(connected ? 'badge-success' : 'badge-secondary');
    badge.textContent = text;
  }

  async function renderQrValueToImg(value) {
    const img = document.getElementById('js-qr-img');
    const empty = document.getElementById('js-qr-empty');
    if (!img || !empty) return;

    if (!value) {
      img.style.display = 'none';
      empty.style.display = '';
      return;
    }

    const isDataUri = value.startsWith('data:image');
    const looksBase64 = /^[A-Za-z0-9+/=]+$/.test(value);

    if (isDataUri) {
      img.src = value;
      img.style.display = '';
      empty.style.display = 'none';
      return;
    }

    if (looksBase64) {
      img.src = 'data:image/png;base64,' + value;
      img.style.display = '';
      empty.style.display = 'none';
      return;
    }

    // senão: trata como "code" e gera QR
    try {
      const dataUrl = await QRCode.toDataURL(value, { margin: 1, width: 360 });
      img.src = dataUrl;
      img.style.display = '';
      empty.style.display = 'none';
    } catch (e) {
      img.style.display = 'none';
      empty.style.display = '';
    }
  }

  async function refreshStatus() {
    try {
      const res = await fetch(statusUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });

      const data = await res.json();
      if (!data || !data.ok || !data.hasInstance) return;

      const connected = !!data.connected;
      const needsQr = !!data.needsQr;
      const state = data.state || '';

      setOverlay(connected);
      setBadge(connected ? 'Conectado' : (needsQr ? 'Aguardando QR' : (state || 'Sem conexão')), connected);

      await renderQrValueToImg(data.qrCode || '');

      if (window.feather) feather.replace();
    } catch (e) {}
  }

  async function requestQr() {
    try {
      const res = await fetch(requestQrUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': @json(csrf_token())
        }
      });

      const data = await res.json();

      // ✅ renderiza imediatamente se o controller devolver qrCode
      if (data && data.ok && data.qrCode) {
        await renderQrValueToImg(data.qrCode);
      }

      setTimeout(refreshStatus, 800);
      setTimeout(refreshStatus, 2000);
    } catch (e) {}
  }

  setInterval(refreshStatus, 10000);

  if (btnRefresh) btnRefresh.addEventListener('click', refreshStatus);
  if (btnRequestQr) btnRequestQr.addEventListener('click', requestQr);

  refreshStatus();
})();
</script>
@endpush
