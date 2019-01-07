<p align="center">
<a href="https://travis-ci.org/SHA2017-badge/Hatchery"><img src="https://travis-ci.org/SHA2017-badge/Hatchery.svg" alt="Build Status"></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery/maintainability"><img src="https://api.codeclimate.com/v1/badges/d11aea44f07d8945e76e/maintainability" /></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery/test_coverage"><img src="https://api.codeclimate.com/v1/badges/d11aea44f07d8945e76e/test_coverage" /></a>
<a href="https://www.codacy.com/app/annejan/Hatchery"><img src="https://api.codacy.com/project/badge/Grade/fd1f9360910d4b6f966f528af1f3568b" alt="Codacy Badge"></a>
</p>

## SHA2017 Hatchery

Simple micropython software repository for the SHA2017 Badge. 

[Live Site](http://badge.team) |
[Documentation](https://wiki.sha2017.org/w/Projects:Badge/Hatchery) |
[Project Wiki](https://wiki.sha2017.org/w/Projects:Badge) |
[GitHub](https://github.com/SHA2017-badge/)

## License

Hatchery is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

## Installation

Requires PHP 7.1 or later!

```bash
cp .env.example .env
```
Edit your database, mail and other settings..

```bash
pip install pyflakes
composer install
php artisan key:generate
php artisan migrate
yarn
yarn production
```
Compiling and installing the patched minigzip 

```bash
wget http://zlib.net/zlib-1.2.11.tar.gz
tar xvf zlib-1.2.11.tar.gz
cd zlib-1.2.11
./configure
echo -e "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h
make
sudo make install
```
Running the development server.


```bash
php artisan serve
```

## API

Apps
```
/eggs/get/[app]/json          - get json data for a the egg named [app]
/eggs/list/json               - a list of all eggs with name, slug, description, revision
/eggs/search/[words]/json     - json data for search query [words]
/eggs/categories/json         - json list of categories
/eggs/category/[cat]/json     - json data for category [cat]
```

Events (via `schedule.py`)
```
/schedule/schedule.json       - version and dates
/schedule/day/[0-4].json      - names and guids
/schedule/event/[guid].json   - info about events
/schedule/fahrplan/[0-4].json - time, duration and tile
```

App specific
```
/weather                      - weather proxied from darksky.net
```

## Running tests
 
 Run all the tests
 
     phpunit
 
 Run a test suite (for a list of availabe suites, see `/phpunit.xml`)
 
     phpunit --testsuite <suite_name>
 
 Run a specific test file
 
     phpunit tests/<optional_folders>/TestFileName
 
 Run a specific test case
 
     phpunit --filter <test_case_name>
 
 Generate code coverage
 
     phpunit --coverage-html docs/coverage
 
 This will create the code coverage docs in `docs/coverage/index.html`

