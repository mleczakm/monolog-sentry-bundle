<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle;

use Dziki\MonologSentryBundle\DependencyInjection\Compiler\MonologHandlerOverridePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MonologSentryBundle
 *
 * @property Extension extension
 */
class MonologSentryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MonologHandlerOverridePass());
    }
}
