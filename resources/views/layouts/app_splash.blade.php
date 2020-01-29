<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="/manifest.json">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrfToken" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <meta name="theme-color" content="#f00">
    <link rel="icon" type="image/x-icon" sizes="16x16" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="640x640" href="/img/bs.png">
    <link rel="apple-touch-icon" sizes="640x640" href="/img/bs.png">

    <!-- Styles -->
    <link href="{{ asset('css/app.css', !App::environment(['local', 'testing'])) }}" rel="stylesheet">
	
    <style>
	html {
		background-color: white;
	}
	.links > a {
		color: #636b6f;
		padding: 0 25px;
		letter-spacing: .1rem;
		text-transform: uppercase;
	}	

	h1.hatcher {
		font-size: 76px;
		font-weight: 400;
	}
	.table-condensed th {
		text-align: center;
	}
	.spacer { 
		height: 2em;
	}
    </style>
    <script>
        window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
    </script>
</head>
<body>
    @include('partials.messages')
    <img class="logo" src="/img/bs.png" alt="Badge.r & smol snek" />
    @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js', !App::environment(['local', 'testing'])) }}"></script>

    @yield('script')
</body>
</html>
