<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

    <title>
        {{ ($tenant->nome_fantasia ?? 'Conectta RH') }} | @yield('title', 'Dashboard')
    </title>

    <!-- Vendors Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

    <!-- Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
    <div id="loader"></div>

    {{-- HEADER --}}
    @include('partials.header')

    {{-- MENU --}}
    @include('partials.menu')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper overflow-visible">
        <div class="container-full">

            <!-- Content Header (Page header) -->
            <div class="content-header">
                @yield('content_header')
            </div>

            <!-- Main content -->
            @yield('content')
            <!-- /.content -->

        </div>
    </div>
    <!-- /.content-wrapper -->

    {{-- FOOTER --}}
    @include('partials.footer')

    <div class="control-sidebar-bg"></div>
</div>

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/vendor_components/apexcharts-bundle/dist/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor_components/jvectormap/lib2/jquery-jvectormap-2.0.2.min.js') }}"></script>
<script src="https://fastly.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
<script src="{{ asset('assets/vendor_components/OwlCarousel2/dist/owl.carousel.js') }}"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- ✅ Simple Toastr Alerts (GLOBAL) --}}
<script src="{{ asset('assets/js/pages/toastr.js') }}"></script>
<script src="{{ asset('assets/js/pages/notification.js') }}"></script>

<script>
(function () {
    // Feather (inclusive para páginas renderizadas via AJAX)
    if (window.feather) feather.replace();

    // Dispara toastr global via session flash
    function fire(type, msg) {
        try {
            if (window.toastr && typeof toastr[type] === 'function') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000,
                    positionClass: 'toast-top-right'
                };
                toastr[type](msg);
                return;
            }
        } catch (e) {}

        // fallback
        alert(msg);
    }

    @if(session('success'))
        fire('success', @json(session('success')));
    @endif

    @if(session('error'))
        fire('error', @json(session('error')));
    @endif

    @if(session('warning'))
        fire('warning', @json(session('warning')));
    @endif

    @if(session('info'))
        fire('info', @json(session('info')));
    @endif
})();
</script>

@stack('scripts')

</body>
</html>
