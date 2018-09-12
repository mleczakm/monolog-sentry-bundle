<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection;

use Dziki\MonologSentryBundle\Processor\TagAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\CachedParser;
use Dziki\MonologSentryBundle\UserAgent\NativeParser;
use Dziki\MonologSentryBundle\UserAgent\PhpUserAgentParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
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
                      ->setPrivate(true)
                      ->addTag('kernel.event_subscriber')
                      ->addTag('monolog.processor')
            ;
        }

        if ($configs['user_agent_parser']) {
            $handlerName = $configs['user_agent_parser'];
            switch ($handlerName) {
                case 'native':
                    $parserClass = NativeParser::class;
                    $container->setDefinition($parserClass, new Definition($parserClass))
                              ->setPrivate(true)
                    ;
                    break;
                case 'phpuseragent':
                    $parserClass = PhpUserAgentParser::class;
                    $container->setDefinition($parserClass, new Definition($parserClass))
                              ->setPrivate(true)
                    ;
                    break;
                default:
                    $parserClass = $handlerName;
            }

            if ($configs['cache']) {
                $container->setDefinition(
                    CachedParser::class,
                    new Definition(
                        CachedParser::class,
                        [
                            new Reference($configs['cache']),
                            new Reference($parserClass),
                        ]
                    )
                )->setPrivate(true)
                ;

                $parserClass = CachedParser::class;
            }

            $container->setDefinition(
                'dziki.monolog_sentry_bundle.browser_data_appending_subscribed_processor',
                new Definition(BrowserDataAppending::class, [new Reference($parserClass)])
            )
                      ->setPrivate(true)
                      ->addTag('kernel.event_subscriber')
                      ->addTag('monolog.processor')
            ;
        }

        if (\is_array($configs['tags'])) {
            foreach ($configs['tags'] as $tag => ['value' => $value, 'name' => $name]) {
                $tagName = $name ?: $tag;
                $container->setDefinition(
                    "dziki.monolog_sentry_bundle.{$tag}_appending_processor",
                    new Definition(
                        TagAppending::class,
                        [
                            $tagName,
                            $value,
                        ]
                    )
                )
                          ->setPrivate(true)
                          ->addTag('monolog.processor')
                ;
            }
        }
    }
}
