<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>@yield('title', 'ConecttaRH')</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @include('partials.header')
  @include('partials.menu')

  <div class="content-wrapper overflow-visible">
    <div class="container-full">
      <section class="content">
        @yield('content')
      </section>
    </div>
  </div>

  @include('partials.footer')

  <div class="control-sidebar-bg"></div>
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

{{-- Toastr (já existe no seu template de login) --}}
<script src="{{ asset('assets/js/pages/toastr.js') }}"></script>
<script src="{{ asset('assets/js/pages/notification.js') }}"></script>

<script>
  // Toastr padrão do sistema (todas as telas)
  @if(session('toastr'))
    (function () {
      const t = @json(session('toastr'));
      // t: {type:'success|error|info|warning', message:'...'}
      if (window.toastr && t?.message) {
        toastr.options.closeButton = true;
        toastr.options.progressBar = true;
        toastr.options.timeOut = 4000;
        toastr[t.type || 'info'](t.message);
      } else if (t?.message) {
        alert(t.message);
      }
    })();
  @endif
</script>

@stack('scripts')
</body>
</html>
