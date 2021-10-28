# Laravel Activity Log

Package allows to collect desired information about user activities and store them into database. It also provides set
of tools to filter activities for presentation purposes.

# Installation

You can install package via composer. Add repository to your composer.json

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mindz-team/laravel-activity-log"
        }
    ],

And run

    composer require mindz-team/laravel-activity-log

Publish migration file

    php artisan vendor:publish --provider="Mindz\LaravelActivityLog\LaravelActivityLogServiceProvider" --tag="config"

Run 

    php artisan migrate 

# Usage

To start logging changes in your model you need to use a `\Mindz\LaravelActivityLog\Traits\LogActivity` trait on it

## Events

Data is being logged when one of 3 events occurs `created`,`updated` or `deleted`.

## Attributes

You can freely shape attributes you want to log in. By default, attributes that will be logged are all visible
attributes. To change that you can simply use method `logStructure` inside desired model.

    public function logStructure()
    {
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "email"=> $this->email
        ];
    }

But instead of array you can also use `JsonResource` object

    public function logStructure()
    {
        return new UserResource($this);
    }

## Storage

All logged data are stored in database `activity`.

## Search

To searching through logs you can use `Activity` model and prepare simple eloquent query.

    Activity::whereBetween('created_at', $dates);

To simplify browsing you can use predefined scopes `scopeCausedBy` and `scopeForSubject`

    Activity::whereBetween('created_at', $dates)->causedBy($user)->forSubject($someModel);

# Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

# Security

If you discover any security related issues, please email r.szymanski@mindz.it instead of using the issue tracker.

# Credits

Author: Roman Szymański [r.szymanski@mindz.it](mailto:r.szymanski@mindz.it)

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
