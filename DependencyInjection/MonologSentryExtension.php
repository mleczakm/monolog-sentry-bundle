<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection;

use Dziki\MonologSentryBundle\Processor\TagAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\NativeParser;
use Dziki\MonologSentryBundle\UserAgent\Parser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MonologSentryExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);

        if ($configs['user_context']) {
            $container->setDefinition(
                'dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor',
                new Definition(UserDataAppending::class, [new Reference(TokenStorageInterface::class)])
            )
                      ->addTag('kernel.event_subscriber')
                      ->addTag('monolog.processor')
            ;
        }

        if ($configs['browser_agent']) {
            $container->setDefinition(Parser::class, new Definition(NativeParser::class))
                      ->setPrivate(true)
            ;

            $container->setDefinition(
                'dziki.monolog_sentry_bundle.browser_data_appending_subscribed_processor',
                new Definition(BrowserDataAppending::class, [new Reference(Parser::class)])
            )
                      ->setPrivate(true)
                      ->addTag('kernel.event_subscriber')
                      ->addTag('monolog.processor')
            ;
        }

        if ($configs['server_name']) {
            $container->setDefinition(
                'dziki.monolog_sentry_bundle.server_name_appending_processor',
                new Definition(
                    TagAppending::class,
                    [
                        'environment',
                        $configs['server_name'],
                    ]
                )
            )
                      ->setPrivate(true)
                      ->addTag('monolog.processor')
            ;
        }

        if ($configs['app_revision']) {
            $container->setDefinition(
                'dziki.monolog_sentry_bundle.app_revision_appending_processor',
                new Definition(
                    TagAppending::class,
                    [
                        'commit',
                        $configs['app_revision'],
                    ]
                )
            )
                      ->setPrivate(true)
                      ->addTag('monolog.processor')
            ;
        }

        if ($configs['symfony_version']) {
            $container->setDefinition(
                'dziki.monolog_sentry_bundle.symfony_version_appending_processor',
                new Definition(
                    TagAppending::class,
                    [
                        'symfony_version',
                        Kernel::VERSION,
                    ]
                )
            )
                      ->setPrivate(true)
                      ->addTag('monolog.processor')
            ;
        }
    }
}
