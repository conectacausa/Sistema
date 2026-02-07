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
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $e)
                      <li>{{ $e }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <div class="row">
                <div class="col-12">
                  <h4 class="mb-15">Configurações</h4>
                </div>
              </div>

              <div class="row">
                <div class="col-12">

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-geral" role="tab">
                          <i data-feather="lock"></i>
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
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-whatsapp" role="tab">
                          <i data-feather="user"></i>
                          WhatsApp (Evolution)
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB: Geral --}}
                      <div class="tab-pane active" id="tab-geral" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-10">Geral</h5>
                          <p class="text-muted mb-0">
                            Central de configurações do sistema (Tela ID {{ $telaId }}).
                          </p>
                        </div>
                      </div>

                      {{-- TAB: Usuários (placeholder) --}}
                      <div class="tab-pane" id="tab-usuarios" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-10">Usuários</h5>
                          <p class="text-muted mb-0">Em breve: configurações gerais de usuários.</p>
                        </div>
                      </div>

                      {{-- TAB: WhatsApp --}}
                      <div class="tab-pane" id="tab-whatsapp" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-15">Integração WhatsApp (Evolution)</h5>

                          <form id="form-whatsapp" method="POST" action="{{ route('config.whatsapp_integracoes.store', ['sub' => $sub]) }}">
                            @csrf

                            <div class="row">
                              <div class="col-12 col-lg-6">
                                <div class="form-group">
                                  <label class="form-label">Base URL (Evolution)</label>
                                  <input type="text"
                                         name="base_url"
                                         class="form-control"
                                         placeholder="https://evolution.conecttarh.com.br"
                                         value="{{ old('base_url', $integracaoWhatsapp->base_url ?? '') }}">
                                </div>
                              </div>

                              <div class="col-12 col-lg-6">
                                <div class="form-group">
                                  <label class="form-label">Instance Name</label>
                                  <input type="text"
                                         name="instance_name"
                                         class="form-control"
                                         placeholder="ex: admin"
                                         value="{{ old('instance_name', $integracaoWhatsapp->instance_name ?? '') }}">
                                </div>
                              </div>

                              <div class="col-12 col-lg-6">
                                <div class="form-group">
                                  <label class="form-label">API Key (Evolution)</label>
                                  <input type="password"
                                         name="api_key"
                                         class="form-control"
                                         placeholder="Cole a API KEY do Evolution"
                                         value="{{ old('api_key', '') }}">
                                  <small class="text-muted">
                                    Por segurança, não exibimos a chave salva. Cole novamente para atualizar.
                                  </small>
                                </div>
                              </div>

                              <div class="col-12 col-lg-6">
                                <div class="form-group mt-25">
                                  <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="whatsapp-ativo"
                                           name="ativo"
                                           value="1"
                                           {{ old('ativo', ($integracaoWhatsapp->ativo ?? true) ? 1 : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp-ativo">
                                      Integração ativa
                                    </label>
                                  </div>

                                  <div class="mt-10">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-test-whatsapp">
                                      Testar conexão
                                    </button>
                                    <span class="ms-10 text-muted" id="whatsapp-test-result"></span>
                                  </div>
                                </div>
                              </div>
                            </div>

                            {{-- Botão Salvar fora do conteúdo das abas: aqui fica no final do form --}}
                            <div class="mt-20">
                              <button type="submit" class="btn btn-primary">
                                Salvar
                              </button>
                            </div>
                          </form>

                        </div>
                      </div>

                    </div>{{-- tab-content --}}
                  </div>{{-- vtabs --}}

                </div>
              </div>

            </div>{{-- box-body --}}
          </div>{{-- box --}}

        </div>
      </div>

    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const btn = document.getElementById('btn-test-whatsapp');
    const result = document.getElementById('whatsapp-test-result');

    if (btn) {
      btn.addEventListener('click', async function () {
        result.textContent = 'Testando...';

        try {
          const res = await fetch("{{ route('config.whatsapp_integracoes.test', ['sub' => $sub]) }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}",
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          const data = await res.json();

          if (data.ok) {
            result.textContent = data.message || 'Conexão OK';
          } else {
            result.textContent = data.message || 'Falha ao testar conexão';
          }
        } catch (e) {
          result.textContent = 'Erro ao testar conexão';
        }

        if (window.feather) feather.replace();
      });
    }

    if (window.feather) feather.replace();
  })();
</script>
@endpush
