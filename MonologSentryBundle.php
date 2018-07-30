<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle;

use Dziki\MonologSentryBundle\DependencyInjection\Compiler\MonologHandlerOverwritePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MonologSentryBundle
 * @package Dziki\MonologSentryBundle
 * @property Extension extension
 */
class MonologSentryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MonologHandlerOverwritePass());
    }
}
