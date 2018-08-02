<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\DependencyInjection\Compiler;

use Dziki\MonologSentryBundle\DependencyInjection\Compiler\MonologHandlerOverwritePass;
use Dziki\MonologSentryBundle\Handler\Raven;
use Monolog\Handler\RavenHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MonologHandlerOverwritePassTest extends TestCase
{
    /**
     * @test
     */
    public function doNothingIfNoRavenHandlerServiceDefined(): void
    {
        $containerBuilder = new ContainerBuilder();
        $expected = spl_object_hash($containerBuilder);

        $monologOverwritePass = new MonologHandlerOverwritePass();
        $monologOverwritePass->process($containerBuilder);

        self::assertEquals($expected, spl_object_hash($containerBuilder));
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

        $monologOverwritePass = new MonologHandlerOverwritePass();
        $monologOverwritePass->process($containerBuilder);

        foreach ($oldClasses as $id => $class) {
            if ($class === RavenHandler::class) {
                $this->assertSame(
                    Raven::class,
                    $containerBuilder
                        ->getDefinition($id)
                        ->getClass()
                );
            } else {
                $this->assertSame(
                    $class,
                    $containerBuilder
                        ->getDefinition($id)
                        ->getClass()
                );
            }
        }
    }
}
