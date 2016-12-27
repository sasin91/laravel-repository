# Laravel 5 Repositories
#### Simply repositories without all the fluff...

Laravel 5 Repositories is used to abstract the data layer, making our application more flexible to maintain.

## Installation

### Composer

Execute the following command to get the latest version of the package:

```terminal
composer require sasin91/laravel-repository
```

Note, to pull this in you might need to set your minimum stability in composer.json
```composer.json
"minimum-stability":"dev",
```

### Laravel

In your `config/app.php` add `Sasin91\LaravelRepository\RepositoryServiceProvider::class` to the end of the `Package Service Providers` array:

```php
'providers' => [
    ...
    Sasin91\LaravelRepository\RepositoryServiceProvider::class,
],
```

Publish Configuration

```shell
php artisan vendor:publish
```

#### Commands

To generate everything you need for your Model, run this command:

```terminal
php artisan make:repository UserRepository {--generic} {--database=?} {--model=?}
```
if no database or model option is provided with the generic option,
it'll attempt to guess a model, in your App namespace.

@note: the model option is really just an alias to the database option.