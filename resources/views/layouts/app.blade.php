<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'ERP - Dashboard')</title>

  <!-- SB Admin 2 CSS -->
  <link href="{{ asset('assets/sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">
  <!-- FontAwesome / vendor if needed -->
  <link href="{{ asset('assets/sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  @stack('styles')
</head>
<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">
    <!-- Sidebar -->
    @include('partials.sidebar')
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <!-- Main Content -->
      <div id="content">
        @include('partials.topbar')

        <!-- Begin Page Content -->
        <div class="container-fluid">
          @yield('content')
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- End of Main Content -->

      @include('partials.footer')
    </div>
    <!-- End of Content Wrapper -->
  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
  </a>

  <!-- SB Admin 2 JS (jQuery + Bootstrap are included in vendor) -->
  <script src="{{ asset('assets/sb-admin-2/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/sb-admin-2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
  <script src="{{ asset('assets/sb-admin-2/js/sb-admin-2.min.js') }}"></script>
  @stack('scripts')
</body>
</html>
