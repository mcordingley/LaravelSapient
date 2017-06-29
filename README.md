# Laravel Sapient

Sapient-compatible bindings for Laravel.

## Installation

Install via composer with `composer require mcordingley/laravel-sapient` and then add
`MCordingley\LaravelSapient\SapientServiceProvider::class` to your `app.php` configuration file. Then, publish the
configuration file with `php artisan vendor:publish`. After publishing the configuration, you'll need to generate keys
for the functions that you intend to use.

## Generating Keys

For sealing responses and unsealing requests, run `php artisan sapient:generate:seal:pair`. Signing and verifying with a
shared key needs `php artisan sapient:generate:shared:authentication`. `php artisan sapient:generate:shared:encryption`
generates a shared key for encryption and decryption. Finally, `php artisan sapient:generate:sign:pair` will create keys
for signing responses and verifying requests. 

## Using the Middleware

This library exposes middleware to perform its various functions, but does not prescribe where to use them. You will
need to place them where they best make sense in the scope of your own project. There are a few restrictions on
where the middlware can meaningfully be placed.

`SharedVerifyRequest` and `VerifyRequest` should be run as early as possible, before anything that depends on the
incoming request processes. If the request does not contain a Message Authentication Code (MAC) in its headers or
contains an invalid one, these will abort the request.

`SharedDecryptRequest` and `UnsealRequest` should be run after the middleware that verify MACs, but before anything that
depends on the content of the request body.

`SharedEncryptResponse` and `SealResponse`should be run as the last middleware that modify the request body, as these
lock the body contents.

`SharedAuthenticateResponse` and `SignResponse` should only run after the response body is done being modified. This
includes any middleware here that performs encryption. Otherwise, their signatures will be invalid. They will each add a
header to the response containing a MAC for the response body. 

## Middleware With Additional Requirements

`VerifyRequest` and `SealResponse` depend on the public keys of the client making the request. Since which key is needed
and where the keys are stored can vary, these middleware cannot directly resolve the keys that they need from Laravel's
container. You will have to provide the logic to return the appropriate public keys.

Create an implementation of `MCordingley\LaravelSapient\KeyResolver\Resolver` that resolves the Base64UrlSafe-encoded
public key needed and register it into Laravel's container with contextual binding. In one of your service providers,
this may look like:

    $this->app->when(MCordingley\LaravelSapient\Middleware\VerifyRequest::class)
        ->needs(MCordingley\LaravelSapient\KeyResolver\Resolver::class)
        ->give(Your\Implementation::class);

`UserResolver` is provided to cover the common case where keys are stored directly on the incoming request's `User`
 object. Simply provide it with the property name where the key is stored and register it into Laravel's container. For
 example:

    $this->app->when(MCordingley\LaravelSapient\Middleware\SealResponse::class)
        ->needs(MCordingley\LaravelSapient\KeyResolver\Resolver::class)
        ->give(function () {
            return new MCordingley\LaravelSapient\KeyResolver\UserResolver('sealing_public_key'); 
        });

`StaticResolver` is also provided. It will resolve whatever key is passed into its constructor. It exists primarily for
testing purposes, but may be of use in niche circumstances or if you want to place key-resolution logic directly into
your service provider.

## Using Your Stored Keys With Sapient

If your project is also using Sapient for creating requests to other servers and processing their responses, you can and
should use your generated keys with it. Simply use Laravel's container to resolve Sapient's cryptography key objects.
They will be loaded with your keys from your configuration.