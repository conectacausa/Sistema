@php
    use Illuminate\Support\Str;

    // Tenant/config vindos do middleware
    $config = app()->bound('tenant.config') ? app('tenant.config') : null;

    // Converte caminho do banco em URL pública
    $toUrl = function ($path) {
        if (!$path) return null;

        $path = trim((string) $path);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (Str::startsWith($path, 'storage/app/public/')) {
            $path = Str::replaceFirst('storage/app/public/', 'storage/', $path);
            return asset($path);
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::replaceFirst('public/', 'storage/', $path);
            return asset($path);
        }

        if (Str::startsWith($path, ['tenants/', 'tenant/', 'uploads/'])) {
            return asset('storage/' . $path);
        }

        return asset($path);
    };

    // Logo horizontal (LIGHT) do banco
    $logoFromDb = $toUrl($config->logo_horizontal_light ?? null);

    // Fallback atual (o que você já usa hoje)
    $fallbackLogo = asset('assets/images/logo-light-text2.png');

    // URL final
    $logoFinal = $logoFromDb ?: $fallbackLogo;
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Conectta RH - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- CSS do template -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

    <!-- TOASTR CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/toastr/toastr.min.css') }}">
</head>

<body class="hold-transition theme-primary bg-img"
      style="background-image: url('{{ asset('assets/images/auth-bg/bg-1.jpg') }}')">

<div class="container h-p100">
    <div class="row align-items-center justify-content-md-center h-p100">
        <div class="col-12">
            <div class="row justify-content-center g-0">
                <div class="col-lg-5 col-md-6 col-12">
                    <div class="bg-white rounded10 shadow-lg">

                        <!-- LOGO (ajustada para usar a do banco) -->
                        <div class="content-top-agile p-30 text-center">
                            <img
                                src="{{ $logoFinal }}"
                                alt="Conectta RH"
                                style="max-width: 220px"
                                onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';"
                            >
                        </div>

                        <!-- FORM -->
                        <div class="p-40">
                            <form method="POST" action="{{ route('login.post') }}">
                                @csrf

                                <!-- CPF -->
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent">
                                            <i class="ti-user"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="cpf"
                                            id="cpf"
                                            class="form-control ps-15 bg-transparent"
                                            placeholder="CPF"
                                            value="{{ old('cpf') }}"
                                            required
                                        >
                                    </div>
                                </div>

                                <!-- SENHA -->
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent">
                                            <i class="ti-lock"></i>
                                        </span>
                                        <input
                                            type="password"
                                            name="password"
                                            class="form-control ps-15 bg-transparent"
                                            placeholder="Senha"
                                            required
                                        >
                                    </div>
                                </div>

                                <!-- OPÇÕES -->
                                <div class="row mb-20">
                                    <div class="col-6">
                                        <div class="checkbox">
                                            <input type="checkbox" id="remember" name="remember">
                                            <label for="remember">Lembrar</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- BOTÃO -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-danger w-100">
                                        Entrar
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- /FORM -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS DO TEMPLATE -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- TOASTR JS (BIBLIOTECA REAL) -->
<script src="{{ asset('assets/vendor/toastr/toastr.min.js') }}"></script>

<!-- MÁSCARA CPF -->
<script>
document.getElementById('cpf')?.addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
});
</script>

<!-- TOASTR MENSAGENS -->
<script>
window.addEventListener('load', function () {

    const mensagem =
        @json(session('toastr_error')
        ?? session('error')
        ?? ($errors->any() ? $errors->first() : null));

    if (!mensagem) return;

    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000
        };
        toastr.error(mensagem);
    } else {
        alert(mensagem);
        console.error('Toastr não carregou corretamente.');
    }
});
</script>

</body>
</html>
