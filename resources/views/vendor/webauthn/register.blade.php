@extends('layouts.app')

@section('content')
    <main class="py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xs-12 col-md-6">
            <div class="card">
              <div class="card-header">{{ trans('webauthn::messages.register.title') }}</div>

              <div class="card-body">
                <div class="alert alert-danger d-none" role="alert" id="error"></div>
                <div class="alert alert-success d-none" role="alert" id="success">
                  {{ trans('webauthn::messages.success') }}
                </div>

                <h3 class="card-title">
                  {{ trans('webauthn::messages.insertKey') }}
                </h3>

                <p class="card-text text-center">
                  <img src="https://ssl.gstatic.com/accounts/strongauth/Challenge_2SV-Gnubby_graphic.png" alt=""/>
                </p>

                <p class="card-text">
                  {{ trans('webauthn::messages.buttonAdvise') }}
                  <br />
                  {{ trans('webauthn::messages.noButtonAdvise') }}
                </p>

                <a href="/" class="card-link" aria-pressed="true">{{ trans('webauthn::messages.cancel') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('webauthn.create') }}" id="form">
        @csrf
        <input type="hidden" name="register" id="register">
        <input type="hidden" name="name" id="name" value="{{ $name }}">
      </form>

    </main>
@endsection

@section('script')
  <script src="{!! secure_asset('vendor/webauthn/webauthn.js') !!}"></script>
  <script>
    var publicKey = {!! json_encode($publicKey) !!};

    var errors = {
      key_already_used: "{{ trans('webauthn::errors.key_already_used') }}",
      key_not_allowed: "{{ trans('webauthn::errors.key_not_allowed') }}",
      not_secured: "{{ trans('webauthn::errors.not_secured') }}",
      not_supported: "{{ trans('webauthn::errors.not_supported') }}",
    };

    function errorMessage(name, message) {
      switch (name) {
      case 'InvalidStateError':
        return errors.key_already_used;
      case 'NotAllowedError':
        return errors.key_not_allowed;
      default:
        return message;
      }
    }

    function error(message) {
      $('#error').text(message).removeClass('d-none');
    }

    var webauthn = new WebAuthn((name, message) => {
       error(errorMessage(name, message));
    });

    if (! webauthn.webAuthnSupport()) {
      switch (webauthn.notSupportedMessage()) {
        case 'not_secured':
          error(errors.not_secured);
          break;
        case 'not_supported':
          error(errors.not_supported);
          break;
      }
    }

    webauthn.register(
      publicKey,
      function (datas) {
        $('#success').removeClass('d-none');
        $('#register').val(JSON.stringify(datas)),
        $('#form').submit();
      }
    );
  </script>
@endsection
