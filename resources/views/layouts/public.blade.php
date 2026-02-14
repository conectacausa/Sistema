<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'ConecttaRH')</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>
<body class="hold-transition light-skin theme-primary">
  <div class="container-full">
    @yield('content')
  </div>

  <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
  <script src="{{ asset('assets/js/template.js') }}"></script>
</body>
</html>
