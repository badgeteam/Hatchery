@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header"><strong>Two-factor authentication</strong></div>
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
                        @if(is_null($data['user']->google2fa_secret))
                            <p>If you want to enable two-factor authentication for your account, complete the following steps</p>
                            <strong>
                                <ol>
                                    <li>Press the button below to generate a QR code, scan it with your app</li>
                                    <li>Enter the OTP code that your app generates</li>
                                </ol>
                            </strong>
                            <form class="" method="POST" action="{{ route('generate2faSecret') }}">
                                @csrf
                                <div class="form-group">
                                    <div class="col-lg-6 offset-md-">
                                        <button type="submit" class="btn btn-primary">Generate secret key to enable 2FA</button>
                                    </div>
                                </div>
                            </form>
                        @elseif(!$data['user']->google2fa_enabled)
                            <strong>1. Scan this QR code with your app:</strong>
                            <br>
                            {!! $data['google2fa_url'] !!}
                            <br>
                            <br> <strong>2. Enter the generated OTP code to enable 2FA</strong>
                            <br>
                            <br>
                            <form class="" method="POST" action="{{ route('enable2fa') }}">
                                @csrf
                                <div class="form-group{{ $errors->has('verify-code') ? ' has-error' : '' }}">
                                    <label for="verify-code" class="col-lg-4 form-control-label">Authenticator Code</label>
                                    <div class="col-lg-6">
                                        <input id="verify-code" type="number" class="form-control" name="verify-code" required>
                                        @if ($errors->has('verify-code'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('verify-code') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-6 offset-md-">
                                        <button type="submit" class="btn btn-primary">Enable 2FA</button>
                                    </div>
                                </div>
                            </form>
                        @elseif($data['user']->google2fa_enabled)
                            <div class="alert alert-success">2FA is currently <strong>enabled</strong> four your account.</div>
                            <p>If you want to disable two-factor authentication.
                                Confirm your password and click on the 2FA disable button.</p>
                            <form class="" method="POST" action="{{ route('disable2fa') }}">
                                @csrf
                                <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                                    <label for="current-password" class="col-lg-4 form-control-label">Current password</label>
                                    <div class="col-lg-6">
                                        <input id="current-password" type="password" class="form-control" name="current-password" autocomplete="current-password" required>
                                        @if ($errors->has('current-password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('current-password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6 offset-md-">
                                    <button type="submit" class="btn btn-primary ">Disable 2FA</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
