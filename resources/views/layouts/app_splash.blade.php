<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ App::environment('local') ? asset('css/app.css') : secure_asset('css/app.css') }}" rel="stylesheet">
	
    <style>
	html, body {
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

    @yield('content')

    <!-- Scripts -->
    <script src="{{ App::environment('local') ? asset('js/app.js') : secure_asset('js/app.js') }}"></script>

    @yield('script')
</body>
</html>
