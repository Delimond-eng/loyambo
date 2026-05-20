@extends('layouts.app')

@section('content')
    <div class="row authentication mx-0">
        <div class="col-xxl-7 col-xl-7 col-lg-12">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                    <div>
                        <p class="h5 fw-semibold mb-2 d-lg-none text-center d-sm-block">Millenium PAY PORTAL</p>
                        <p class="fw-normal mb-4 fs-10 op-7 text-center d-lg-none d-sm-block"> Veuillez effectuer le paiement en toute sécurité avec <b class="text-primary">Millenium Pay</b></p>
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <ul class="nav nav-tabs flex-row justify-content-center align-items-center vertical-tabs-3" role="tablist"> 
                                    <li class="nav-item" role="presentation"> 
                                        <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#employee-vertical" aria-selected="true"> 
                                        <i class="ri-smartphone-line me-2 align-middle d-inline-block"></i>Mobile money </a> 
                                    </li> 
                                    <li class="nav-item" role="presentation"> <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#customers-vertical" aria-selected="false" tabindex="-1"> 
                                        <i class="ri-bank-card-line me-2 align-middle d-inline-block"></i>Carte bancaire </a> 
                                    </li> 
                                </ul>
                            </div>

                            <div class="col-xl-12">
                                <div class="mx-2" id="errors-section">
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="tab-content"> 
                                    <div class="tab-pane show active text-muted" id="employee-vertical" role="tabpanel"> 
                                        <form id="mobileForm" class="row gy-3">
                                            <div class="col-xl-12 mt-0">
                                                <label for="amount" class="form-label text-default">Montant à payer 
                                                </label>
                                                <div class="d-flex">
                                                    <input type="number" id="amount" value="{{ $amount }}" name="amount" class="form-control form-control-lg border-primary w-100 me-1"  placeholder="enter amount" readonly>
                                                    <select name="currency" style="width: 100px" class="form-control border-1 border-primary" name="currency" id="currency" disabled>
                                                        <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD</option>
                                                        <option value="CDF" {{ $currency == 'CDF' ? 'selected' : '' }}>CDF</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="text" hidden class="form-control form-control-lg"
                                                    id="customer_id" name="uuid" value="{{ $uuid }}" placeholder="enter customer_id ID">
                                            <div class="col-xl-12"> 
                                                <label for="phone" class="form-label text-default d-flex align-items-center">Numero mobile money
                                                    <div class="d-flex ms-3">
                                                        <img style="height:30px" class="me-1" src="{{ asset("assets/images/M-pesa-logo-2.png") }}" alt="visa">
                                                        <img style="height:30px" class="me-1" src="{{ asset("assets/images/orange-money-logo.png") }}" alt="visa">
                                                        <img style="height:30px" src="{{ asset("assets/images/airtel.png") }}" alt="visa">
                                                    </div> 
                                                </label>
                                                <div class="d-flex">
                                                    <input class="form-control me-1 text-primary" type="tel" value="+243" style="width: 70px" readonly>
                                                    <input name="phone" type="text" class="form-control form-control-lg"
                                                    id="phone" type="tel" value="{{ $phone }}" placeholder="Entrer votre numero mobile money" maxlength="9" required> </div>
                                                </div>

                                            <div class="col-xl-12 d-grid mt-3">
                                                <button id="mobileSubmitBtn" class="btn btn-lg btn-primary" type="submit"><span id="mobileBtnLoader" class="spinner-border spinner-border-sm align-middle d-none" role="status" aria-hidden="true"></span> Poursuivre le paiement</button>
                                            </div>
                                        </form>
                                    </div> 
                                    <div class="tab-pane text-muted" id="customers-vertical" role="tabpanel"> 
                                        <form id="cardForm" class="row">
                                            <div class="col-xl-12 mt-0">
                                                <label for="amount" class="form-label text-default d-flex align-items-center">Montant à payer 
                                                    <div class="d-flex ms-3">
                                                        <img style="height:30px" class="me-1" src="{{ asset("assets/images/visa.png") }}" alt="visa">
                                                        <img style="height:30px" src="{{ asset("assets/images/mastercard.png") }}" alt="visa">
                                                    </div> 
                                                </label>
                                                <div class="d-flex">
                                                    <input type="number" id="amount" value="{{ $amount }}" name="amount" class="form-control form-control-lg border-primary w-100 me-1"  placeholder="enter amount" readonly>
                                                    <select name="currency" style="width: 100px" class="form-control border-1 border-primary" name="currency" id="currency" disabled>
                                                        <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD</option>
                                                        <option value="CDF" {{ $currency == 'CDF' ? 'selected' : '' }}>CDF</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 d-grid mt-3">
                                                <button id="cardSubmitBtn" class="btn btn-lg btn-dark" type="submit"><span id="cardBtnLoader" class="spinner-border spinner-border-sm align-middle d-none" role="status" aria-hidden="true"></span> Poursuivre le paiement</button>
                                            </div>
                                        </form>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-5 col-xl-5 col-lg-5 d-xl-block d-none px-0">
            <div class="authentication-cover">
                <div class="aunthentication-cover-content rounded">
                    <div
                        class="swiper keyboard-control swiper-initialized swiper-horizontal swiper-pointer-events swiper-backface-hidden">
                        <div class="swiper-wrapper"
                             aria-live="off">
                            <div
                                class="text-fixed-white text-center p-5 d-flex align-items-center justify-content-center">
                                <div>
                                    <div class="mb-5"> <img src="{{ asset('assets/images/tap-to-pay.png') }}"
                                            class="authentication-image" alt=""> </div>
                                    <h6 class="fw-semibold text-fixed-white">Millenium PAY PORTAL</h6>
                                    <p class="fw-normal fs-14 op-7"> Veuillez effectuer le paiement en toute sécurité avec <b class="text-primary">Millenium Pay</b></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="{{ asset('assets/js/app/pay_manage.js') }}"></script>
@endpush

@push("styles")
    <style>
        .nav-tabs .nav-link {
            display: inline-flex !important;
            align-items: center;
        }
        .nav-tabs {
            flex-wrap: nowrap !important; /* empêche le retour à la ligne */
            overflow-x: auto;             /* si trop serré sur mobile → scroll horizontal */
        }
    </style>
@endpush

