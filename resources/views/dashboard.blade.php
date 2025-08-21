@extends("layouts.admin")

@section("content")
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Tableau de bord</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                               <li class="breadcrumb-item ms-1" aria-current="page">Bienvenue {{ Auth::user()->name }}, Vous êtes connectés comme <span class="text-primary fw-700">{{ Auth::user()->role }}</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                @canCloseDay
                    @can("cloturer-journee")
                    <a href="{{ route("orders.portal") }}" class="waves-effect waves-light btn btn-danger text-center btn-rounded"><i class="fa fa-sign-out"></i> Clôturer la journée</a>
                    @endcan
                @else
                    @can("ouvrir-journee")  
                    <button  class="btn-start-day waves-effect waves-light btn btn-primary text-center btn-rounded"><i class="fa fa-sign-in"></i> Commencer la journée</button>
                    @endcan
                @endif
            </div>
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="row g-0 py-2">
                            <div class="col-12 col-lg-3">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-User fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                                        Utilisateurs connectés
                                    </span>
                                    <span class="text-primary fs-40">845</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar" role="progressbar" style="width: 35%; height: 4px;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3 hidden-down">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Selected-file fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                                        Factures journalières
                                    </span>
                                    <span class="text-info fs-40">952</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 55%; height: 4px;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-12 col-lg-3">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Info-circle fs-40"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span><br>
                                        Commandes annulées
                                    </span>
                                    <span class="text-warning fs-40">845</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 65%; height: 4px;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="box-body">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Cart2 d-block fs-40"><span class="path1"></span><span class="path2"></span></span>
                                        Ventes journalières
                                    </span>
                                    <span class="text-danger fs-40">158</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 40%; height: 4px;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="col-12">
                    <div class="box">
                        <div class="box-header p-4">
							<h4 class="box-title"><span class="text-primary fw-600">Liste des commandes journalières</span></h4>
						</div>
                        <div class="box-body">
                            <div class="table-responsive rounded card-table">
                                <table class="table border-no" id="example1">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Customer Name</th>
                                            <th>Location</th>
                                            <th>Amount</th>
                                            <th>Status Order</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hover-primary">
                                            <td>#245879</td>
                                            <td>14 April 2021,<span class="fs-12"> 03:13 AM</span></td>
                                            <td>Aaliyah clark</td>
                                            <td>1623 E Updahl Ct, Harrison, ID, 83833</td>
                                            <td>$124.6</td>
                                            <td>												
                                                <span class="badge badge-pill badge-primary-light">Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245880</td>
                                            <td>25 April 2021,<span class="fs-12"> 11:22 AM</span></td>
                                            <td>Boone Doe</td>
                                            <td>261 Poplar Ave, Devon, PA, 19333</td>
                                            <td>$74.99</td>
                                            <td>
                                                <span class="badge badge-pill badge-danger-light">New Order</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245881</td>
                                            <td>25 April 2021,<span class="fs-12"> 11:52 AM</span></td>
                                            <td>Carlie Paton</td>
                                            <td>8959 State 405 Rte, Maceo, KY, 42355</td>
                                            <td>$66.21</td>
                                            <td>
                                                <span class="badge badge-pill badge-primary-light">Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245882</td>
                                            <td>27 April 2021,<span class="fs-12"> 02:25 PM</span></td>
                                            <td>Delilah</td>
                                            <td>4480 Ka Haku Rd, Princeville, HI, 96722 </td>
                                            <td>$89.32</td>
                                            <td>
                                                <span class="badge badge-pill badge-danger-light">New Order</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245883</td>
                                            <td>27 April 2021,<span class="fs-12"> 02:30 PM</span></td>
                                            <td>Hannah Doe</td>
                                            <td>128 Mclemore Rd, Taft, TN, 38488</td>
                                            <td>$85.2</td>
                                            <td>
                                                <span class="badge badge-pill badge-primary-light">Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245884</td>
                                            <td>27 April 2021,<span class="fs-12"> 12:42 AM</span></td>
                                            <td>Emerson Clark</td>
                                            <td>505 E 14th St, Scotland Neck, NC, 27874</td>
                                            <td>$18.5</td>
                                            <td>
                                                <span class="badge badge-pill badge-primary-light">Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245885</td>
                                            <td>27 April 2021,<span class="fs-12"> 12:32 AM</span></td>
                                            <td>Crystal Doe</td>
                                            <td>312 S Judd St, Sioux City, IA, 51103</td>
                                            <td>$125.2</td>
                                            <td>
                                                <span class="badge badge-pill badge-primary-light">Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245886</td>
                                            <td>29 April 2021,<span class="fs-12"> 11:12 AM</span></td>
                                            <td>Jenny don</td>
                                            <td>4381 Rutledge Pike, Rutledge, TN, 37861</td>
                                            <td>$39.25</td>
                                            <td>
                                                <span class="badge badge-pill badge-warning-light">On Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245887</td>
                                            <td>29 April 2021,<span class="fs-12"> 10:35 AM</span></td>
                                            <td>Joanne Clark</td>
                                            <td>Po Box 232, Bimble, KY, 40915</td>
                                            <td>$55.2</td>
                                            <td>
                                                <span class="badge badge-pill badge-warning-light">On Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245888</td>
                                            <td>30 April 2021,<span class="fs-12"> 10:42 AM</span></td>
                                            <td>Madeline doe</td>
                                            <td>146 Patterson Dr, Hayneville, AL, 36040</td>
                                            <td>$24.55</td>
                                            <td>
                                                <span class="badge badge-pill badge-warning-light">On Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="hover-primary">
                                            <td>#245889</td>
                                            <td>30 April 2020,<span class="fs-12"> 11:12 AM</span></td>
                                            <td>Melinda</td>
                                            <td>143 Portsmouth Cir, Glen Mills, PA, 19342</td>
                                            <td>$78.5</td>
                                            <td>
                                                <span class="badge badge-pill badge-warning-light">On Delivery</span>
                                            </td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-12">
                    <div class="box bg-transparent no-shadow">
                        <div class="box-header pt-0 mb-0  px-0 d-flex align-items-center justify-content-between">
                            <h4 class="box-title">
                                Produits
                            </h4>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i
                                            class="ti-search text-primary"></i></span>
                                    <input type="text" class="form-control ps-15 bg-white"
                                        placeholder="Recherche...">
                                </div>
                            </div>
                        </div>
                        <div class="box-body px-0 pt-0 mt-0">
                            <div class="row">
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-1.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Kung Pao Tofu Recipe</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-2.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Pan Seared Salmon </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-3.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Dal Palak Recipe </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-4.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Vegetable Jalfrezi</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-5.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Palak Paneer Bhurji </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-6.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Kadai Paneer Gravy</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-1.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Gajar Matar Recipe</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-2.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Aloo Tamatar Ki Sabzi </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 d-xxxl-none d-xl-block d-lg-none col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-3.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Vegan Thai Basil</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div> -->
            </div>
        </section>
        <!-- /.content -->
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

