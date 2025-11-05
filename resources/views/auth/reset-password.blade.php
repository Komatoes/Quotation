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

                <h4 class="mb-2">Reset Password 🔒</h4>
                <p class="mb-4">Enter your new password</p>

                <form class="mb-3" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Enter your new password" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password-confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password-confirm"
                               name="password_confirmation" placeholder="Confirm your new password" required>
                    </div>

                    <button class="btn btn-primary d-grid w-100" type="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection