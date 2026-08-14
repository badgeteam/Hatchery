<!--
SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
SPDX-License-Identifier: MIT
-->

# Badge.Team Hatchery

[![Maintainability](https://qlty.sh/gh/badgeteam/projects/Hatchery/maintainability.svg)](https://qlty.sh/gh/badgeteam/projects/Hatchery)
[![Code Coverage](https://qlty.sh/gh/badgeteam/projects/Hatchery/coverage.svg)](https://qlty.sh/gh/badgeteam/projects/Hatchery)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery?ref=badge_shield)
[![Known Vulnerabilities](https://snyk.io/test/github/badgeteam/Hatchery/badge.svg)](https://snyk.io/test/github/badgeteam/Hatchery)
[![Laravel](https://github.com/badgeteam/Hatchery/actions/workflows/laravel.yml/badge.svg)](https://github.com/badgeteam/Hatchery/actions/workflows/laravel.yml)
[![REUSE status](https://api.reuse.software/badge/github.com/badgeteam/Hatchery)](https://api.reuse.software/info/github.com/badgeteam/Hatchery)
Simple micropython software repository for Badges.

[Live Site](https://hatchery.badge.team) \|
[API Playground](https://hatchery.badge.team/api) \|
[Documentation](https://badge.team/docs/esp32-platform-firmware/hatchery/) \|
[GitHub](https://github.com/badgeteam/)

## Installation

-   Requires PHP 8.4 or later
-   Requires Python 3.6 or later
-   Requires Node.js 22 or later
-   Requires Redis 3.2 or later
-   Requires Git 2.8 or later

For deployment on a server.

```bash
cp .env.example .env
```

Edit your database, mail and other settings..

Or copy the local dev environment config.

```bash
cp .env.dev .env
```

Install and configure required items.

```bash
pip install pyflakes
composer install
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

Install assets.

```bash
php artisan storage:link
```

Installing and configuring the async websocket server. Broadcasting goes over
Redis into laravel-echo-server, which is no longer maintained upstream; Laravel
Reverb is the modern replacement, but moving to it is not a drop in change.

```bash
npm install -g laravel-echo-server
laravel-echo-server init
```

Compiling and installing the patched minigzip. Eggs are gzipped with a 13 bit
window so the badges can inflate them, which stock gzip cannot do.

```bash
curl -O https://zlib.net/fossils/zlib-1.2.11.tar.gz
tar xf zlib-1.2.11.tar.gz
cd zlib-1.2.11
./configure
echo -e "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h
make
sudo cp minigzip /usr/local/bin/
```

The `Dockerfile` does the same, pinned to that version and checked against its
sha256, with a mirror to fall back on when zlib.net turns CI traffic away.

If you would like to have Verilog support.

Install [Icarus Verilog](https://iverilog.fandom.com/wiki/Installation_Guide) 0.9 or later.

TODO more info ;)

### Services

You'll need a be running [Laravel Horizon](https://laravel.com/docs/13.x/horizon#deploying-horizon) service.

For the websocket server.

```bash
cp laravel-echo-server.json.example laravel-echo-server.json
laravel-echo-server start
```

`laravel-echo-server.json` is deliberately not in the repository or the release
tarball: it holds host specific paths. Together with `.env` it is one of the two
files a deployment has to carry across by hand, and losing it is silent — the
site keeps working, only live updates stop.

Two settings need attention:

-   `keyPrefix` must match the prefix Laravel puts on its Redis keys, or the
    server subscribes to a pattern nothing publishes to. That prefix defaults to
    `Str::slug(APP_NAME) . '-database-'`, so it changes if `APP_NAME` does.
    Ask the application rather than guessing:

    ```bash
    php artisan tinker --execute='echo config("database.redis.options.prefix"), PHP_EOL;'
    ```

-   The `ssl*Path` entries are read once at startup, so the server keeps serving
    the certificate it started with. Restart it after a renewal, or it will
    eventually be offering an expired one.

To check a running server, from anywhere:

```bash
curl 'https://hatchery.example.com:6001/socket.io/?EIO=4&transport=polling'
```

A session id comes back when it is healthy. `"devMode": true` logs every channel
it sees, which is the quickest way to confirm events are arriving — note that it
prints the channel name before the key prefix is stripped, so the prefix showing
up there is expected and not a misconfiguration.

### Running the development server locally

After going through the steps

```bash
php artisan serve
```

If you don't want to install things and do the above steps, Docker makes all the above as easy as:

```bash
docker compose up # -d for daemon mode
docker exec -it hatchery-laravel-1 php artisan migrate --seed
docker exec -it hatchery-laravel-1 npm run watch
```

Enjoy your Hatchery at <http://localhost:8000>

## [API](docs/API.md)

See: <https://hatchery.badge.team/api>

## Running tests

Three suites, and they cover different things. The PHP tests need a database,
the browser tests need the application running.

### PHP

```bash
vendor/bin/pest                          # everything
vendor/bin/pest --testsuite Unit         # one suite, see phpunit.xml
vendor/bin/pest tests/Unit/IconTest.php  # one file
vendor/bin/pest --filter "resized"       # one case
```

Clear the caches first if the app has been run in between:

```bash
php artisan route:clear && php artisan config:clear
```

### JavaScript

```bash
npm test              # vitest, unit tests for the editor and icon helpers
npm run test:watch
```

### Browser

Playwright drives a real Chromium against a running Hatchery. It needs the
fixtures, and it starts the server itself unless `APP_BASE_URL` points at one.

```bash
php artisan db:seed --class=E2eSeeder --force
npx playwright install --with-deps chromium
npm run test:e2e
```

These cover the parts only a browser can answer: that the editor mounts and
saves, that the public file view is read only, and that the editor bundle stays
off pages that do not need it.

### Static analysis and style

```bash
vendor/bin/phpstan analyse                 # level 8
vendor/bin/phpcs -q --warning-severity=0
vendor/bin/phpcbf                          # fix what it can
npm run lint
```

### Coverage

```bash
vendor/bin/pest --coverage                        # summary in the terminal
vendor/bin/pest --coverage-html docs/coverage     # browsable report
npm run coverage                                  # JavaScript
```

CI publishes both to [Qlty](https://qlty.sh/gh/badgeteam/projects/Hatchery).

The JavaScript figure is lower than it looks because `app.js` and `bootstrap.js`
are browser entry points: they are exercised by the Playwright tests, which are
not instrumented, so they count as uncovered here. Everything a unit test can
reasonably reach is covered.

## Upload limits

Files are stored in the database, so a large upload has to get past four
separate limits. The Docker image and `docker-compose.yaml` set all of these;
a hand rolled deployment needs them too.

| limit | where | needs to be |
| --- | --- | --- |
| `upload_max_filesize` | php.ini | at least the ceiling |
| `post_max_size` | php.ini | a little above it |
| `memory_limit` | php.ini | file is read into a string |
| `max_allowed_packet` | MariaDB | one file is one big `INSERT` |

The ceiling itself is `App\Models\File::MAX_UPLOAD_MEGABYTES`, which the
uploader and the validation rule both read, so raising it is a one line change
plus matching server settings.

## Licensing

Hatchery follows the [REUSE](https://reuse.software/spec-3.3/) specification, so
every file states its copyright and licence.

- Source files carry an SPDX header:

  ```php
  // SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
  // SPDX-License-Identifier: MIT
  ```

- Files that cannot hold a comment (images, JSON, lock files) and files that came
  from elsewhere are annotated in `REUSE.toml`.
- Full licence texts live in `LICENSES/`.

New files need a header. Check before pushing:

```bash
docker run --rm -v "$PWD:/data" fsfe/reuse lint
```

A few assets predate this and could not be traced; they are marked
`LicenseRef-Unidentified` in `REUSE.toml` with a note on what each is suspected
to be. If you recognise one, please correct its entry.

## License

Hatchery is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery?ref=badge_large)
