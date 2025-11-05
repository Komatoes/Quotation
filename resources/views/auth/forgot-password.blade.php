@extends('layouts.app')

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
    <div class="authentication-inner row">
        <div class="d-flex col-12 col-lg-5 align-items-center authentication-bg p-sm-5 p-4">
            <div class="w-px-400 mx-auto">
                <div class="app-brand mb-4">
                    <a href="/" class="app-brand-link gap-2 mb-2">
                        <span class="app-brand-logo demo">
                            <img src="{{ asset('assets/img/logo/favicon.png') }}" height="50" alt="Logo">
                        </span>
                        <span class="app-brand-text demo h3 mb-0 fw-bold">Quotation System</span>
                    </a>
                </div>

                <h4 class="mb-2">Forgot Password? 🔒</h4>
                <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="mb-3" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                               placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button class="btn btn-primary d-grid w-100" type="submit">Send Reset Link</button>
                </form>
                <div class="text-center">
                    <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                        <i class="ti ti-chevron-left"></i>
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection