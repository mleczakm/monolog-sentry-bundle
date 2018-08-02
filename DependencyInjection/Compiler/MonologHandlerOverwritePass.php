<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\DependencyInjection\Compiler;

use Dziki\MonologSentryBundle\Handler\Raven;
use Monolog\Handler\RavenHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MonologHandlerOverwritePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        array_map(
            function (Definition $definition) {
                if ($definition->getClass() === RavenHandler::class) {
                    $definition->setClass(Raven::class);
                }
            },
            $container->getDefinitions()
        );
    }
}
