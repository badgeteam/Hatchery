<p align="center">
<a href="https://travis-ci.org/badgeteam/Hatchery"><img src="https://travis-ci.org/badgeteam/Hatchery.svg" alt="Build Status"></a>
<a href="https://codeclimate.com/github/badgeteam/Hatchery/maintainability"><img src="https://api.codeclimate.com/v1/badges/05fc2bac5b3669fa1b0c/maintainability" /></a>
<a href="https://codeclimate.com/github/badgeteam/Hatchery/test_coverage"><img src="https://api.codeclimate.com/v1/badges/05fc2bac5b3669fa1b0c/test_coverage" /></a>
<a href="https://www.codacy.com/app/Badgeteam/Hatchery?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=badgeteam/Hatchery&amp;utm_campaign=Badge_Grade"><img src="https://api.codacy.com/project/badge/Grade/78402ccc553245f2be2d1def6fdc3c68" alt="Codacy Badge"></a>
</p>

## Badge.Team Hatchery

Simple micropython software repository for Badges. 

[Live Site](http://badge.team) |
[Documentation](https://wiki.badge.team/Hatchery) |
[Project Wiki](https://wiki.badge.team) |
[GitHub](https://github.com/badgeteam/)

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

