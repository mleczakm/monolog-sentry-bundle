<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('monolog_sentry_bundle');

        $rootNode
            ->children()
            ->booleanNode('user_context')
            ->defaultValue(true)
            ->end()
            ->booleanNode('browser_agent')
            ->defaultValue(true)
            ->end()
            ->booleanNode('server_name')
            ->defaultValue('%env(SERVER_NAME)%')
            ->end()
            ->booleanNode('app_revision')
            ->defaultValue('%env(APP_REVISION)%')
            ->end()
            ->booleanNode('symfony_version')
            ->defaultValue(true)
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

{

}
