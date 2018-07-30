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
                ->scalarNode('browser_agent')
                    ->info('phpuseragent (default), native or id of custom service implementing Parser interface')
                    ->defaultValue('phpuseragent')
                ->end()
                ->arrayNode('tags')
                    ->arrayPrototype()
                        ->beforeNormalization()
                        ->ifString()
                        ->then(function ($value) {
                            return array('value' => $value);
                        })
                    ->end()
                    ->children()
                        ->scalarNode('value')
                            ->isRequired()
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
