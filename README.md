<p align="center">
<a href="https://travis-ci.org/SHA2017-badge/Hatchery"><img src="https://travis-ci.org/SHA2017-badge/Hatchery.svg" alt="Build Status"></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery"><img src="https://img.shields.io/codeclimate/coverage/github/SHA2017-badge/Hatchery.svg" alt="Code Coverage"></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery"><img src="https://img.shields.io/codeclimate/github/SHA2017-badge/Hatchery.svg" alt="Code Climate GPA"></a>
<a href="https://www.codacy.com/app/annejan/Hatchery"><img src="https://api.codacy.com/project/badge/Grade/fd1f9360910d4b6f966f528af1f3568b" alt="Codacy Badge"></a>
</p>

## SHA2017 Hatchery

Simple micropython software repository for the SHA2017 Badge. 

[Live Site](http://badge.sha2017.org) |
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
npm install
npm run production
```

## API

```
/eggs/get/[app]/json       - get json data for a the egg named [app]
/eggs/list/json            - a list of all eggs with name, slug, description, revision
/eggs/search/[words]/json  - json data for search query [words]
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
  