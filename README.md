# Monolog Sentry Bundle

[![Build Status](https://travis-ci.org/mleczakm/monolog-sentry-bundle.svg?branch=master)](https://travis-ci.org/mleczakm/monolog-sentry-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d/mini.png)](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d)

Bundle for appending useful data to log records like username, parsed user-agent header, host name, Symfony version, 
commit hash and many more - you can provide custom tags to be added to all your logs.

## Installation

Install bundle with `composer require dziki/monolog-sentry-bundle` command.

## Enable the Bundle

Add entry to `config/bundles.php`:

```php
return [
    ...
    Dziki\MonologSentryBundle\MonologSentryBundle::class => ['all' => true],
    ...
];

```

or to `app/AppKernel.php`

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Dziki\MonologSentryBundle\MonologSentryBundle(),
        );

        // ...
    }

    // ...
}
```

## Configuration

Default configuration looks like that:
```yaml
dziki.monolog_sentry_bundle:
    user_context: true # append username from TokenStorage to log
    browser_agent: phpuseragent # parser browser name, version and platform from user agent
``` 
Needs two environment variables to be set: `SERVER_NAME` with desired environment name in Sentry panel and 
`APP_REVISION` with commit hash. Settings any of this value to `false` will turn off log processors.

## Custom tags

You can extend it by adding custom tags. For example, for logging Symfony version, environment and server name
you can modify config to this:
```yaml
monolog_sentry:
    user_context: true
    browser_agent: true
    tags:
        symfony_version: !php/const Symfony\Component\HttpKernel\Kernel::VERSION # useful for regression
        commit: '%env(APP_REVISION)%' # hash of commit
        environment: '%env(SERVER_NAME)%' # Sentry environment discriminator, much more useful than default `prod`
```

## User Agent parser

Bundle support two parser:
- [github.com/donatj/PhpUserAgent](https://github.com/donatj/PhpUserAgent) as default, no config needed
- native [get_browser()](https://php.net/manual/en/function.get-browser.php) - browscap configuration setting in php.ini 
must point to the correct location of the [browscap.ini](https://browscap.org/)

Configurable through `browser_agent` value, respectively `phpuseragent` or `native`. You can also add own, by providing
name of service implementing [Parser](https://github.com/mleczakm/monolog-sentry-bundle/blob/master/UserAgent/Parser.php)
interface.

## Cache support

To be added.


