@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-md-">
                <div class="card">
                    <div class="card-header">Two-factor authentication</div>
                    <div class="card-body">
                        <p>Two-factor authentication (2FA) enhances access security by requesting two methods
                            (also known as factors) to verify your identity. Two-factor authentication protects
                            against phishing, social engineering and password brute force attacks and protects
                            your logins against attackers who abuse weak or stolen login details.</p>
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                            <strong>Enter the OTP code from your app</strong>
                        <br>
                        <br>
                        <form class="" action="{{ route('2faVerify') }}" method="POST">{{ csrf_field() }}
                            <div class="form-group{{ $errors->has('one_time_password-code') ? ' has-error' : '' }}">
                                <label for="one_time_password" class="col-lg-4 form-control-label">One Time Password</label>
                                <div class="col-lg-6">
                                    <input name="one_time_password" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-6 offset-md-">
                                    <button class="btn btn-primary" type="submit">Authenticate</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
