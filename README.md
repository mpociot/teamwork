# Teamwork (Laravel 5 Package)

[![Build Status](https://travis-ci.org/mpociot/teamwork.svg)](https://travis-ci.org/mpociot/teamwork)

Teamwork is the fastest and easiest method to add a User / Team association with Invites to your **Laravel 5** project.

**This package is still under development. Until a 1.0 Version is released, the API is likely to change.**

## Installation

In order to install Laravel 5 Teamwork, just add 

    "mpociot/teamwork": "dev-master"

to your composer.json. Then run `composer install` or `composer update`.

Then in your `config/app.php` add 

    'Mpociot\Teamwork\TeamworkServiceProvider'
    
The `Teamwork` Facade will be installed automatically within the Service Provider.

Run the `vendor:publish` command

    $ php artisan vendor:publish --provider="Mpociot\Teamwork\TeamworkServiceProvider"

This will publish the `config/teamwork.php` and the Teamwork migration file to your application.

Run the `dumpautoload` command

    $ composer dumpautoload -o

Run the `migration` command

    $ php artisan migrate
    
    
Add the `TeamworkUserTrait` trait to your User model:

```php
<?php namespace App;

...
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Teamwork\Traits\TeamworkUserTrait;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes, TeamworkUserTrait;
}
```

    
## License

Teamwork is free software distributed under the terms of the MIT license.