# laravel-u2f
[![Code Climate](https://codeclimate.com/github/certly/laravel-u2f/badges/gpa.svg)](https://codeclimate.com/github/certly/laravel-u2f) [![Build Status](https://travis-ci.org/certly/laravel-u2f.svg?branch=master)](https://travis-ci.org/certly/laravel-u2f)

Integrate FIDO U2F into Laravel 5.4.x applications. You'll need to be accessing your application from an [App ID compliant](https://developers.yubico.com/U2F/App_ID.html) domain; `localhost` support is finicky.

## Install
``` bash
composer require certly/laravel-u2f
```

In `config/app.php`, add the provider `Certly\U2f\U2fServiceProvider::class,` to the providers array and `'U2f' => Certly\U2f\U2fServiceProvider::class,` to the aliases array.


### Publishing
This will copy the needed resources (views, CSS, and JavaScript) to your project so you can modify them.

``` bash
php artisan vendor:publish --provider="Certly\U2f\U2fServiceProvider"
php artisan migrate
```

You can add the following to your `mix.scripts` call if you're using Elixir to automatically include the needed JavaScript in your central JavaScript file.

``` javascript
elixir(function(mix) {
    mix.scripts([
        // ...
        'u2f/app.js',
        'u2f/u2f.js',
```

The included views assume there is an `app` view. If this isn't the case, you'll need to manually modify the views in the `u2f` folder (under `resources/views`).

### Middleware

In the `app/Http/Kernel.php` file, add the following to `$routeMiddleware`. This middleware is called internally by the package, so it is important that it is present.

``` php
protected $routeMiddleware = [
    // ...
    'u2f' => \App\U2f\Http\Middleware\U2f::class,
];
```

## Usage

### Middleware
``` php
    Route::get('admin/profile', ['middleware' => ['auth', 'u2f'], function () {
        //
    }]);
```
### Configuration
`config/u2f.php` is commented and will be created when you publish the provider via the above command.

## Security
Security issues can be reported via [HackerOne](https://hackerone.com/certly) or via email at [ian@certly.io](mailto:ian@certly.io).

## Credits
- [Arnaud LAHAXE](https://github.com/lahaxearnaud)
- [Ian Carroll](https://github.com/iangcarroll)

## License  
[MIT](LICENSE.md)
