@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item">Reset Password</li>
                </ol>
            </div>
        </div>
    </section>
    <section class=" section-10">
        <div class="container">
            @if (Session::has('success'))
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                </div>
            @endif
            @if (Session::has('error'))
                <div class="alert alert-danger">
                    {{ Session::get('error') }}
                </div>
            @endif
            <div class="login-form">
                <form action="{{ route('account.processResetPassword') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" id="token" value="{{ $token }}">
                    <h4 class="modal-title">Reset Password</h4>
                    <div class="form-group">
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                            placeholder="Please insert your new password" name="new_password">
                        @error('new_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control @error('confirm_new_password') is-invalid @enderror"
                            placeholder="Please confirm your new password" name="confirm_new_password">
                        @error('confirm_new_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="text-center">
                        <input type="submit" class="btn btn-dark btn-block btn-lg" value="Submit">
                    </div>
                </form>
            </div>
        </div>
        </div>
    </section>
@endsection
