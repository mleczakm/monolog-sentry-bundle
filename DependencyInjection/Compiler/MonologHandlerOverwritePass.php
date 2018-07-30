<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection\Compiler;

use Dziki\MonologSentryBundle\Handler\Raven;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MonologHandlerOverwritePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('monolog.handler.sentry');
        $definition->setClass(Raven::class);
    }
}
