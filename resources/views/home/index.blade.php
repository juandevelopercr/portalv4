@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')

<!-- Vendor Styles -->
@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/toastr/toastr.scss', 'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/animate-on-scroll/animate-on-scroll.scss',
'resources/assets/vendor/libs/spinkit/spinkit.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js',
'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/toastr/toastr.js',
'resources/assets/vendor/libs/sortablejs/sortable.js',
'resources/assets/vendor/libs/animate-on-scroll/animate-on-scroll.js',
'resources/js/custom.js'])
@endsection

@push('scripts')
  <script src="{{ asset('vendor/fusioncharts/fusioncharts.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/fusioncharts.charts.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/fusioncharts.powercharts.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/fusioncharts.widgets.js') }}"></script>

  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.candy.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.carbon.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.fint.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.gammel.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.ocean.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.umber.js') }}"></script>
  <script src="{{ asset('vendor/fusioncharts/themes/fusioncharts.theme.zune.js') }}"></script>

  <script src="{{ asset('js/honorarios-mes-handler.js') }}"></script>
@endpush

@section('content')
  @can('view-admin-dashboard')

  @endcan
@endsection
