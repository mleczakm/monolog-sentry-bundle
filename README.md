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
monolog_sentry:
    user_context: true # append username from TokenStorage to log
    user_agent_parser: phpuseragent # parser browser name, version and platform from user agent
``` 

You can turn off logging user context and/or parsing browser by setting any of this values to `false`.

## Caching once parsed User Agents

Caching is supported when service implementing `Psr\SimpleCache\CacheInterface` is provided under `cache`:

```yaml
monolog_sentry:
    cache: app.default_cache # service implementing "Psr\SimpleCache\CacheInterface" interface
``` 

## Custom tags

You can extend amount of logged data by adding custom tags. For example, for logging Symfony version, setting 
useful [Sentry environment](https://docs.sentry.io/learn/environments/) and server name you should modify config to this:

```yaml
monolog_sentry:
    user_context: true
    user_agent_parser: phpuseragent
    tags:
        symfony_version: !php/const Symfony\Component\HttpKernel\Kernel::VERSION # useful for regression check
        commit: '%env(APP_REVISION)%' # for example hash of commit, set your own
                                      #  environment variable or parameter
        environment: '%env(SERVER_NAME)%' # Sentry environment discriminator, much more useful than default `prod`
```

## User Agent parser

Bundle support two parser:
- `phpuseragent` ([github.com/donatj/PhpUserAgent](https://github.com/donatj/PhpUserAgent)) as default, no config needed
- `native` ([get_browser()](https://php.net/manual/en/function.get-browser.php)) - browscap configuration setting in php.ini 
must point to the correct location of the [browscap.ini](https://browscap.org/)

Configurable through `user_agent_parser` value, respectively `phpuseragent` or `native`. You can also add own, by providing
name of service implementing [Parser](https://github.com/mleczakm/monolog-sentry-bundle/blob/master/UserAgent/Parser.php)
interface.

## Milestones to stable release

- [x] POC
- [x] custom tags
- [x] unit tests
- [x] breadcrumbs support
- [x] cache adapter
- [ ] functional tests


