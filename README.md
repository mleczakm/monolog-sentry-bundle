# Monolog Sentry Bundle

[![Build Status](https://travis-ci.org/mleczakm/monolog-sentry-bundle.svg?branch=master)](https://travis-ci.org/mleczakm/monolog-sentry-bundle)
[![Coverage Status](https://coveralls.io/repos/github/mleczakm/monolog-sentry-bundle/badge.svg?branch=master)](https://coveralls.io/github/mleczakm/monolog-sentry-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mleczakm/monolog-sentry-bundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d/mini.png)](https://insight.sensiolabs.com/projects/a441c60e-3cdd-410a-985d-c8abc59a9c1d)

Bundle for appending useful data to log records like username, parsed user-agent header, host name, Symfony version, 
commit hash and many more - you can provide custom tags to be added to all your logs.

## Installation

Install bundle with `composer require dziki/monolog-sentry-bundle` command.

## TL;DR

### Before
![screenshot_20180808_004201](https://user-images.githubusercontent.com/3474636/43806409-207069fa-9aa4-11e8-8483-9e4b511c1457.png)

## After
![screenshot_20180808_002716](https://user-images.githubusercontent.com/3474636/43806415-2a844c0e-9aa4-11e8-81bb-02a7fd6594d1.png)

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
name of service implementing [ParserInterface](https://github.com/mleczakm/monolog-sentry-bundle/blob/master/UserAgent/ParserInterface.php).

## Hints

- Hide your Sentry monolog handler behind `buffer` one to prevent low level messages notifications, but kept them in breadcrumbs:

```yaml
monolog:
    handlers:
        main:
            type:         fingers_crossed
            action_level: error
            handler:      buffered
        buffered:
            type:    buffer
            handler: sentry
        sentry:
            type:    raven
            dsn:     '%env(SENTRY_DSN)%'
            level:   info # logs which will show as breadcrumbs in Sentry issue 
```
- Add Sentry handler `release` option to monolog config for easy regression seeking:
```yaml
monolog:
    handlers:
        ...
        sentry:
            ...
            release: '%env(APP_VERSION)%' # version tag or any release ID
```

## Milestones to stable release

- [x] POC
- [x] custom tags
- [x] unit tests
- [x] breadcrumbs support
- [x] cache adapter
- [x] functional tests
- [ ] valuable functional tests ;)


