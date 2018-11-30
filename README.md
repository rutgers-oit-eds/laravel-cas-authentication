# Laravel CAS Package

## Overview
This package implements phpCAS for Laravel. Unlike other packages, this package implements a custom Laravel auth guard. This allows the developer to leverage the existing Laravel authentication system, while relying on CAS for the actual authentication process.

## Installation Instructions
1) Include the package in your project using composer:

        composer require rutgers-oit-eds/laravel-cas-authentication

2) Publish the assets. laravel-cas comes with the following assets:

    * Config file: CAS - Configures the laravel-cas package
    * Config file: Auth *(optional)* - A customized version of the default Laravel auth configuration, tailored to laravel-cas. You may use this as a guide to edit your own configuration file, or publish the configuration file included in this package.
    * View: Application Logged Out - A default view indicating the user has logged out of the application. Provides a link to fully logout of CAS SSO if the user chooses. Publishing this view allows you to customize the logged out screen to fit your style. The only important element to retain is a link to the CAS logout route: `<a href="/auth/sso_logout">LINK TEXT</a>`
    * View: CAS User Not Authorized - A default view indicating the user successfully authenticated against CAS, but is not present in the user repository, therefore they are not authorized to use the application. Publishing this view allows you to customize the not authorized screen to fit your style. The only important element to retain is a link to the CAS logout route: `<a href="/auth/sso_logout">LINK TEXT</a>`

    To publish only the required assets, you can run:

        php artisan vendor:publish --tag="laravel-cas"

    This will publish the cas.php configuration file and the logged out view.

    To publish the customized auth config, **overwriting your existing auth.php file**, you can run:

        php artisan vendor:publish --force --tag="laravel-cas-authconfig"

3) Add the `Rutgers\Cas\Traits\UsesCasAuthentication` trait to your user model:

    The UsesCasAuthentication trait sets important model properties identifying the authentication identifier to the cas_username field.

    If you choose to have a different model, or more than one model, be CAS-authenticated, you need to update your user providers section of the auth.php configuration. Either update the model field on the existing provider, or add additional providers with your additional models:

        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model' => App\User::class,
            ],
        ]

4) Update your users migration:

    Remove the following fields:

    * `$table->timestamp('email_verified_at')->nullable();`
    * `$table->string('password');`
    * `$table->rememberToken();`

    Add the following field before the existing name field:

    * `$table->string('cas_username')->unique();`

5) Delete the password reset migration that ships with Laravel since CAS is doing authentication.

## Published Routes

This package publishes 3 routes for handling authentication flow.

* `/login`: Triggers the CAS authentication guard's `attempt()` method, which redirects a user out to CAS for authentication. Once the user is redirected back to the application, the authentication guard's `login()` method handles setting the user's session tokens and returning the user to the URL defined in the CAS configuration under `cas_redirect_path`.

* `/logout`: Triggers the application's logout routines, destroying the application session and logging the user out. This does **NOT** log the user out of CAS, per the CAS protocol. The user will be directed to the logged out page, which has a link to the CAS logout if the user wishes to fully log out of SSO. The logged out view is customizable, see step 2 of the [installation instructions](#installation-instructions).

* `/auth/sso_logout`: Triggers the CAS logout routines, directing the user to CAS where they are fully logged out of SSO. By defining the CAS service URL or the CAS logout redirect URL in the configuration, you can control where the user goes after logging out of CAS.