<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\DependencyInjection\Compiler;

use Dziki\MonologSentryBundle\DependencyInjection\Compiler\MonologHandlerOverridePass;
use Dziki\MonologSentryBundle\Handler\Raven;
use Monolog\Handler\RavenHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Dziki\MonologSentryBundle\DependencyInjection\Compiler\MonologHandlerOverridePass
 */
class MonologHandlerOverridePassTest extends TestCase
{
    /**
     * @test
     */
    public function doNothingIfNoRavenHandlerServiceDefined(): void
    {
        $containerBuilder = new ContainerBuilder();
        $expected = \spl_object_hash($containerBuilder);

        $monologOverridePass = new MonologHandlerOverridePass();
        $monologOverridePass->process($containerBuilder);

        self::assertEquals($expected, \spl_object_hash($containerBuilder));
    }

    /**
     * @test
     */
    public function changeClassDefinitionForAllAndOnlyRavenHandlerServices()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                '1' => new Definition(RavenHandler::class),
                '2' => new Definition(RavenHandler::class),
                '3' => new Definition(\stdClass::class),
            ]
        );
        $oldClasses = [];
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $oldClasses[$id] = $definition->getClass();
        }

        $monologOverridePass = new MonologHandlerOverridePass();
        $monologOverridePass->process($containerBuilder);

        foreach ($oldClasses as $id => $class) {
            if (RavenHandler::class === $class) {
                $this->assertSame(
                    Raven::class,
                    $containerBuilder
                        ->getDefinition((string) $id)
                        ->getClass()
                );
            } else {
                $this->assertSame(
                    $class,
                    $containerBuilder
                        ->getDefinition((string) $id)
                        ->getClass()
                );
            }
        }
    }
}
