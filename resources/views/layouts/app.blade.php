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

    <title>{{ request()->getHost() }} {{ config('app.name', 'Hatchery') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css', !App::environment(['local', 'testing', 'docker'])) }}?v=1.0" rel="stylesheet">
    <livewire:styles>

    <meta name="theme-color" content="#F2DAC7">
    <link rel="icon" type="image/x-icon" sizes="16x16" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="640x640" href="{{ asset('img/bs.png') }}">
    <link rel="apple-touch-icon" sizes="640x640" href="{{ asset('img/bs.png') }}">
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        @auth
        window.UserId = {{ Auth::user()->id }};
        @endauth
    </script>

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Hatchery') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        @if(isset($file))
                            <li><a href="{{ route('projects.index') }}">Eggs</a></li>
                            @if(Auth::check())
                                @can('update', $file->version->project)
                                    <li><a href="{{ route('projects.edit', ['project' => $file->version->project->slug]) }}">{{ $file->version->project->name }}</a></li>
                                @else
                                    <li><a href="{{ route('projects.show', ['project' => $file->version->project->slug]) }}">{{ $file->version->project->name }}</a></li>
                                @endcan
                            @else
                            <li><a href="{{ route('projects.show', ['project' => $file->version->project->slug]) }}">{{ $file->version->project->name }}</a></li>
                            @endif
                            <li><a>{{ $file->name }}</a></li>
                        @elseif(isset($project) && !isset($projects))
                            <li><a href="{{ route('projects.index') }}">Eggs</a></li>
                            <li><a>{{ $project->name }}</a></li>
                        @else
                            <li>
                                <a href="{{ route('projects.index') }}">Eggs</a>
                            </li>
                        @endif
                            <li><a href="{{ route('users.index') }}">Users</a></li>
                            <li><a href="{{ route('badges.index') }}">Badges</a></li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('users.edit', Auth::user()->id) }}">Profile</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @include('partials.messages')

        @yield('content')
    </div>
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "url": "{{ url('') }}",
  "name": "Badge.Team Hatchery",
  "logo": "{{ url('/img/bs.png') }}",
  "foundingDate": "2017",
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "support",
    "email": "help@badge.team"
  }
}
    </script>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js', !App::environment(['local', 'testing', 'docker'])) }}?v=1.0"></script>
    <livewire:scripts>

    @yield('script')
    <footer class="bg-light text-center text-lg-start">
        <!-- Copyright -->
        <div class="text-center p-3">
            © {{ date('Y') }} badge.team Hatchery
            <span id="application_version">{{ App\Http\Kernel::applicationVersion() }}</span>
        </div>
        <!-- Copyright -->
    </footer>
</body>
</html>
