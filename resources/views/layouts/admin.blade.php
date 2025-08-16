<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="assets/images/favicon.png">

    <title>Loyambo | Home</title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="assets/css/vendors_css.css">

    <!-- Style-->
    <link rel="stylesheet" href="assets/css/horizontal-menu.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/skin_color.css">
    @stack("styles")
</head>

<body class="layout-top-nav light-skin theme-primary fixed">

    <div class="wrapper">
        <!-- <div id="loader"></div> -->

        @include("components.header")

        @include("components.sidebar")

        @yield("content")

        @include("components.footer")


        <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>

    </div>

    <!-- Vendor JS -->
    <script src="assets/js/vendors.min.js"></script>
    <script src="assets/js/pages/chat-popup.js"></script>
    <script src="assets/vendors/components/apexcharts-bundle/dist/apexcharts.min.js"></script>
    <script src="assets/vendors/icons/feather-icons/feather.min.js"></script>

    <script src="assets/vendors/components/OwlCarousel2/dist/owl.carousel.js"></script>

   <!--  <script src="assets/vendors/lib/4/core.js"></script>
    <script src="assets/vendors/lib/4/maps.js"></script>
    <script src="assets/vendors/lib/4/geodata/worldLow.js"></script>
    <script src="assets/vendors/lib/4/themes/kelly.js"></script>
    <script src="assets/vendors/lib/4/themes/animated.js"></script> -->



    <!-- Riday Admin App -->
    <script src="assets/js/jquery.smartmenus.js"></script>
    <script src="assets/vendors/components/datatable/datatables.min.js"></script>	
    <script src="assets/vendors/components/bootstrap-select/dist/js/bootstrap-select.js"></script>
	<script src="assets/vendors/components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
	<script src="assets/vendors/components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
	<script src="assets/vendors/components/select2/dist/js/select2.full.js"></script>
    <script src="assets/js/menus.js"></script>
    <script src="assets/js/template.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    @stack("scripts")

</body>

</html>