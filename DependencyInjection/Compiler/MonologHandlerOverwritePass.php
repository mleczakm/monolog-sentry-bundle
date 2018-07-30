<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection\Compiler;

use Dziki\MonologSentryBundle\DependencyInjection\Configuration;
use Dziki\MonologSentryBundle\Handler\Raven;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MonologHandlerOverwritePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = $container->getExtensionConfig('monolog_sentry');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $definition = $container->findDefinition($config['monolog_sentry_handler']);
        $definition->setClass(Raven::class);
    }

    private function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
