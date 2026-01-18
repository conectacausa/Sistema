<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }} | Editar Filial</title>
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>
<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
  <div class="wrapper">
    @includeIf('layouts.header')
    @includeIf('layouts.menu')

    <div class="content-wrapper">
      <div class="container-full">
        <div class="content-header">
          <h4 class="page-title">Editar Filial</h4>
        </div>

        <section class="content">
          <div class="box">
            <div class="box-body">
              <p class="text-muted">Tela de edição será implementada na sequência.</p>

              <hr>
              <pre style="white-space: pre-wrap;">{{ json_encode($filial->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
          </div>
        </section>
      </div>
    </div>

    @includeIf('layouts.footer')
  </div>

  <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
  <script src="{{ asset('assets/js/template.js') }}"></script>
</body>
</html>
