<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('monolog_sentry');
        // @formatter:off
        $rootNode
            ->children()
                ->booleanNode('user_context')
                    ->info('whether to log or not username obtained from TokenStorage')
                    ->defaultTrue()
                ->end()
                ->scalarNode('user_agent_parser')
                    ->info('phpuseragent (default), native or id of custom service implementing ParserInterface interface')
                    ->defaultValue('phpuseragent')
                ->end()
                ->scalarNode('cache')
                    ->info('"CacheInterface" implementing cache service ID')
                    ->defaultNull()
                ->end()
                ->arrayNode('tags')
                    ->arrayPrototype()
                        ->beforeNormalization()
                        ->ifString()
                        ->then(function ($value) {
                            return ['value' => $value];
                        })
                    ->end()
                    ->children()
                        ->scalarNode('value')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('name')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }
}
