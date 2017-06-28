<p align="center">
<a href="https://travis-ci.org/SHA2017-badge/Hatchery"><img src="https://travis-ci.org/SHA2017-badge/Hatchery.svg" alt="Build Status"></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery"><img src="https://img.shields.io/codeclimate/coverage/github/SHA2017-badge/Hatchery.svg" alt="Code Coverage"></a>
<a href="https://codeclimate.com/github/SHA2017-badge/Hatchery"><img src="https://img.shields.io/codeclimate/github/SHA2017-badge/Hatchery.svg" alt="Code Climate GPA"></a>
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
composer install
php artisan key:generate
php artisan migrate
npm install
npm run production
pip install pyflakes
```

## API

```
/eggs/get/[app]/json       - get json data for a the egg named [app]
/eggs/list/json            - a list of all eggs with name, slug, description, revision
/eggs/search/[words]/json  - json data for search query [words]
```