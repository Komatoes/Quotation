<!doctype html>

<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="../../assets/"
    data-template="vertical-menu-template-no-customizer" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Demo: Login Basic - Pages | Vuexy - Bootstrap Dashboard PRO</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="../../assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="../../assets/vendor/libs/node-waves/node-waves.css" />

    <link rel="stylesheet" href="../../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <!-- Vendor -->
    <link rel="stylesheet" href="../../assets/vendor/libs/@form-validation/form-validation.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="../../assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="../../assets/js/config.js"></script>
</head>


@extends('layouts.public')

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6">
            <div class="card">
                <div class="card-body">

                    <!-- Title -->
                    <div class="app-brand justify-content-center mb-4">
                        <span class="app-brand-text demo text-heading fw-bold">Access Quotation</span>
                    </div>

                    <h4 class="mb-1 text-center">Please verify your identity</h4>
                    <p class="mb-4 text-center">Enter your details to view this quotation</p>

                    {{-- Show Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ route('quotation.public.validate', ['token' => $token]) }}" id="verifyForm">
                        @csrf

                        <div class="mb-4 form-control-validation">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" class="form-control" name="first_name" placeholder="Enter your first name" required autofocus>
                        </div>

                        <div class="mb-4 form-control-validation">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" class="form-control" name="last_name" placeholder="Enter your last name" required>
                        </div>

                        <div class="mb-4 form-control-validation">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" id="phone_number" class="form-control" name="phone_number" placeholder="e.g. 09123456789" required>
                        </div>

                        <button class="btn btn-primary d-grid w-100" type="submit">
                            Access Quotation
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
