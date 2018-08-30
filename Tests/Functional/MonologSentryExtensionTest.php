<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class MonologSentryExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     * @covers \Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension::load
     */
    public function checkIfServicesDefinedAccordingToConfig(): void
    {
        $this->load(
            [
                'user_context' => true,
                'user_agent_parser' => 'phpuseragent',
                'tags' => [
                    'test' => 'test',
                    'test2' => [
                        'name' => 'test2',
                        'value' => 'test2',
                    ],
                ],
            ]
        );

        $defaultServices = [
            'dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor',
            'dziki\monologsentrybundle\useragent\phpuseragentparser',
            'dziki.monolog_sentry_bundle.browser_data_appending_subscribed_processor',
            'dziki.monolog_sentry_bundle.test_appending_processor',
            'dziki.monolog_sentry_bundle.test2_appending_processor',
        ];

        foreach ($defaultServices as $defaultService) {
            $this->assertContainerBuilderHasService($defaultService);
        }
    }

    protected function getContainerExtensions()
    {
        return [
            new MonologSentryExtension(),
        ];
    }
}
