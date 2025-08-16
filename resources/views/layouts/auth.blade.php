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

    <title>Loyambo Plateforme</title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="assets/css/vendors_css.css">

    <!-- Style-->
    <link rel="stylesheet" href="assets/css/horizontal-menu.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/skin_color.css">

</head>

<body class="hold-transition theme-primary bg-img" style="background-image: url('assets/images/auth-bg/bg-6.jpg')">

    <div class="container h-p100">
        <div class="row align-items-center justify-content-md-center h-p100">
            @yield("content")
        </div>
    </div>

    <!-- Vendor JS -->
    <script src="assets/js/vendors.min.js"></script>
    <script src="assets/vendors/icons/feather-icons/feather.min.js"></script>
    <script src="assets/vendors/components/jquery-toast-plugin-master/src/jquery.toast.js"></script>
    <script src="{{ asset("assets/js/libs/vue2.js") }}"></script>
    @stack("scripts")
</body>

</html>