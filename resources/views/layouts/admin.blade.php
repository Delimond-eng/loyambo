<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset("assets/images/favicon.png") }}">

    <title>Loyambo | Home</title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

    <!-- Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/horizontal-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

    <style>
    @media (min-width: 768px) {
        .layout-top-nav.fixed .content-wrapper {
            padding-top: {{ Auth::user()->role === 'serveur' ? '40px' : '60px' }};
        }
    }

    /* Téléphones (écrans < 768px) */
    @media (max-width: 767.98px) {
        .layout-top-nav.fixed .content-wrapper {
            padding-top: {{ Auth::user()->role === 'serveur' ? '40px' : '60px' }};
        }
    }
    </style>
    @stack("styles")
</head>

<body class="layout-top-nav light-skin theme-primary fixed">

    <div class="wrapper">
        <!-- <div id="loader"></div> -->

        @include("components.header")

        @yield("content")

        @include("components.footer")

        <!-- Modal pour commencer une journée -->
        @include("components.modals.day_start_modal")

        <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>

    </div>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/apexcharts-bundle/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/icons/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/OwlCarousel2/dist/owl.carousel.js') }}"></script>


   <!--  <script src="assets/vendors/lib/4/core.js"></script>
    <script src="assets/vendors/lib/4/maps.js"></script>
    <script src="assets/vendors/lib/4/geodata/worldLow.js"></script>
    <script src="assets/vendors/lib/4/themes/kelly.js"></script>
    <script src="assets/vendors/lib/4/themes/animated.js"></script> -->



    <!-- Riday Admin App -->
    <script src="{{ asset('assets/js/jquery.smartmenus.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/datatable/datatables.min.js') }}"></script>	
    <script src="{{ asset('assets/vendors/components/bootstrap-select/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/select2/dist/js/select2.full.js') }}"></script>
    <script src="{{ asset('assets/js/menus.js') }}"></script>
    <script src="{{ asset('assets/vendors/components/jquery-toast-plugin-master/src/jquery.toast.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
    
    <script src="{{ asset("assets/vendors/plugins/input-mask/jquery.inputmask.js")}}"></script>
	<script src="{{ asset("assets/vendors/plugins/input-mask/jquery.inputmask.date.extensions.js") }}"></script>
	<script src="{{ asset("assets/vendors/plugins/input-mask/jquery.inputmask.extensions.js") }}"></script>
	<script src="{{ asset("assets/vendors/plugins/moment/moment.js") }}"></script>
	<script src="{{ asset("assets/vendors/components/bootstrap-daterangepicker/daterangepicker.js") }}"></script>
	<script src="{{ asset("assets/vendors/components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js") }}"></script>
	<script src="{{ asset("assets/vendors/components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js") }}"></script>
	<script src="{{ asset("assets/vendors/plugins/timepicker/bootstrap-timepicker.min.js") }}"></script>

    <script>
        const startBtn = document.querySelectorAll('.btn-start-day');
        startBtn.forEach(el=>{
            el.addEventListener('click', function(){
                $("body").toggleClass("right-bar-toggle");
            });
        });
    </script>

    <script src="{{ asset("assets/js/libs/vue2.js") }}"></script>

    @stack("scripts")

</body>

</html>