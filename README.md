# Find all models in your Laravel app or package

[![GitHub Tests Action Status](https://github.com/vicgutt/laravel-models-finder/actions/workflows/run-tests.yml/badge.svg)](https://github.com/vicgutt/laravel-models-finder/actions/workflows/run-tests.yml)
[![GitHub PHPStan Action Status](https://github.com/vicgutt/laravel-models-finder/actions/workflows/phpstan.yml/badge.svg)](https://github.com/vicgutt/laravel-models-finder/actions/workflows/phpstan.yml)
[![GitHub Code Style Action Status](https://github.com/vicgutt/laravel-models-finder/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/vicgutt/laravel-models-finder/actions/workflows/fix-php-code-style-issues.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/vicgutt/laravel-models-finder.svg?style=flat-square)](https://packagist.org/packages/vicgutt/laravel-models-finder)
[![Total Downloads](https://img.shields.io/packagist/dt/vicgutt/laravel-models-finder.svg?style=flat-square)](https://packagist.org/packages/vicgutt/laravel-models-finder)

---

This package allows you to find and retrieve all Laravel models in a given folder.
A "model" is any class extending `Illuminate\Database\Eloquent\Model`.

Here's a quick example:

```php
// On an app containing only the default Laravel "User" model, running:
$models = ModelsFinder::find()
    ->map(static fn (ModelData $model): array => [
        'path' => $model->path,
        'class' => $model->class,
    ])
    ->toArray();

// would return the following:
[
    [
        'path' => '/[...]/my-project/app/Models/User.php',
        'class' => '\App\Models\User',
    ],
]
```

## Installation

You can install the package via composer:

```bash
composer require vicgutt/laravel-models-finder
```

## Usage

You can initiate the discovery of models by using the `find` static method.

```php
$models = ModelsFinder::find(
    directory: app_path('Models'),
    basePath: base_path(),
    baseNamespace: '',
);
```

This method accepts 3 optional arguments:

-   `directory`: The directory in which to recusively start searching for models. Defaults to `app_path('Models')`.
-   `basePath`: The autoloaded entry directory of the project where the search will be initiated. Defaults to `base_path()`.
-   `baseNamespace`: The autoloaded base namespace of the project where the search will be initiated. Defaults to `''`.

The `basePath` & `baseNamespace` properties will most likely correspond to an autoloaded entry in a `composer.json` file.
Example:

```jsonc
{
    "autoload": {
        "psr-4": {
            // Base namespace       |  Base path
            "Spatie\\MediaLibrary\\": "src"
        }
    }
}
```

Here's an example showcasing searching for a model in the `vendor` folder:

```php
ModelsFinder::find(
    directory: base_path('vendor/spatie/laravel-medialibrary'),
    basePath: base_path('vendor/spatie/laravel-medialibrary/src'),
    baseNamespace: 'Spatie\MediaLibrary'
)->toArray(),

// would return the following:
[
    [
        'path' => '[...]/vendor/spatie/laravel-medialibrary/src/MediaCollections/Models/Media.php',
        'class' => 'Spatie\MediaLibrary\MediaCollections\Models\Media',
    ],
]
```

The return value of the method is a lazy collection _(`Illuminate\Support\LazyCollection`)_.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

If you're interested in contributing to the project, please read our [contributing docs](https://github.com/vicgutt/laravel-models-finder/blob/main/.github/CONTRIBUTING.md) **before submitting a pull request**.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Victor GUTT](https://github.com/vicgutt)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
