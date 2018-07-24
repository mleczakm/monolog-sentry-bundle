# Monolog Sentry Bundle

[![Build Status](https://travis-ci.org/mleczakm/monolog-sentry-bundle.svg?branch=master)](https://travis-ci.org/mleczakm/monolog-sentry-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d/mini.png)](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d)

Bundle for appending useful data to log records like username, parsed user-agent header, host name, Symfony version and commit hash.

## TL;DR

`composer require dziki/monolog-sentry-bundle` and Your issues in Sentry will have OOTB username, browser, operation system
(based on user agent) and symfony version. With two additional environment/config variables set - You will also obtain 
proper environments and commit hash in tags - just follow the manual.

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
    user_context: true # username
    browser_agent: true # browser name, version and platform
    server_name: '%env(SERVER_NAME)%' # Sentry environment discriminator, much more useful than default `prod`
    app_revision: '%env(APP_REVISION)%' # hash of commit
    symfony_version: true # Symfony kernel version tag - useful for regression
``` 
Needs two environment variables to be set: `SERVER_NAME` with desired environment name in Sentry panel and 
`APP_REVISION` with commit hash. Settings any of this value to `false` will turn off log processors.


