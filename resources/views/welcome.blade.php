<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .content {
                color: #404040;
                font-weight: 600;
            }

            table a {
                color: #404040;
                text-decoration: none;
            }

            table a:hover {
                color: #216a94;
            }


        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/projects') }}">Eggs</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    SHA2017 {{ config('app.name', 'Laravel') }}
                </div>
                <div>
                    Contributors: {{$users}}
                    Eggs: {{$projects}}
                </div>
                <div class="links">
                    <a href="https://wiki.sha2017.org/w/Projects:Badge">Wiki</a>
                    <a href="https://github.com/SHA2017-badge/">GitHub</a>
                </div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Revision</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($published as $project)
                        <tr>
                            <td><a href="{{ route('projects.edit', ['project' => $project->id]) }}">{{ $project->name }}</a></td><td>{{ $project->revision }}</td>
                        </tr>
                    @empty
                        <tr><td>No published eggs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
