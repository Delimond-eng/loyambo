<header class="main-header">
    <div class="inside-header bg-transparent">
        <div class="d-flex align-items-center logo-box justify-content-start">
            <!-- Logo -->
            <a href="{{ route("home") }}" class="logo">
                <!-- logo-->
                <div class="logo-lg">
                    <span class="light-logo"><img src="assets/images/logo-3.jpg" alt="logo"></span>
                    <span class="dark-logo"><img src="assets/images/logo-light-text.png" alt="logo"></span>
                </div>
            </a>
        </div>
        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <div class="app-menu">
                <ul class="header-megamenu nav">
                    <li class="btn-group nav-item d-none d-xl-inline-block">
                        <div class="app-menu">
                            <div class="search-bx mx-5">
                                <!-- <form>
                                    <div class="input-group">
                                        <input type="search" class="form-control" placeholder="Search"
                                            aria-label="Search" aria-describedby="button-addon2">
                                        <div class="input-group-append">
                                            <button class="btn" type="submit" id="button-addon3"><i
                                                    class="ti-search"></i></button>
                                        </div>
                                    </div>
                                </form> -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="navbar-custom-menu r-side">
                <ul class="nav navbar-nav">
                    <li class="btn-group nav-item d-lg-inline-flex d-none">
                        <a href="#" data-provide="fullscreen"
                            class="waves-effect waves-light nav-link full-screen btn-info-light"
                            title="Full Screen">
                            <i class="icon-Expand-arrows"><span class="path1"></span><span
                                    class="path2"></span></i>
                        </a>
                    </li>

                    <!-- User Account-->
                    <li class="dropdown user user-menu">
                        <a href="#"
                            class="dropdown-toggle p-0 text-dark hover-primary ms-md-30 ms-10 d-flex align-items-center"
                            data-bs-toggle="dropdown" title="User">
                            <span class="ps-30 d-md-inline-block d-none"></span>
                            <div class="text-start d-md-inline-block d-none">
                                <strong class="text-white">Gaston delimond</strong><br>
                                <small class="text-white">Administrateur</small>
                            </div>
                            <img src="assets/images/avatar/avatar-2.png"
                                class="user-image rounded-circle avatar bg-white mx-10" alt="User Image">
                        </a>
                        <ul class="dropdown-menu animated flipInX">
                            <li class="user-body">
                                <a class="dropdown-item text-warning" href="#"><i class="fa fa-sign-out text-muted me-2"></i>
                                    Clotûrer la journée</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/login"><i
                                        class="ti-lock text-muted me-2"></i>
                                    Deconnexion</a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
        </nav>
    </div>
</header>