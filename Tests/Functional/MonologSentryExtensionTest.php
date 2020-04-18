<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension;
use Dziki\MonologSentryBundle\Processor\TagAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\CachedParser;
use Dziki\MonologSentryBundle\UserAgent\NativeParser;
use Dziki\MonologSentryBundle\UserAgent\PhpUserAgentParser;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MonologSentryExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     * @covers \Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension::load
     *
     * @uses   \Dziki\MonologSentryBundle\DependencyInjection\Configuration
     */
    public function checkIfServicesDefinedAndPrivate(): void
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
            'dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor' => [
                'class' => UserDataAppending::class,
            ],
            PhpUserAgentParser::class => [
                'class' => PhpUserAgentParser::class,
            ],
            'dziki.monolog_sentry_bundle.browser_data_appending_subscribed_processor' => [
                'class' => BrowserDataAppending::class,
            ],
            'dziki.monolog_sentry_bundle.test_appending_processor' => [
                'class' => TagAppending::class,
            ],
            'dziki.monolog_sentry_bundle.test2_appending_processor' => [
                'class' => TagAppending::class,
            ],
        ];

        $this->registerService(TokenStorageInterface::class, new TokenStorage());
        $this->compile();

        $this->assertServicesDefinedAndPrivate($defaultServices);
    }

    private function assertServicesDefinedAndPrivate($defaultServices): void
    {
        foreach ($defaultServices as $defaultService => $data) {
            $this->assertContainerBuilderHasService($defaultService, $data['class']);
            try {
                $service = $this->container->get($defaultService);

                if (Kernel::MAJOR_VERSION >= 4) {
                    $this->fail(sprintf('Service "%s" (%s) should be private', \get_class($service), $defaultService));
                }
            } catch (ServiceNotFoundException $exception) {
                $this->assertContains($defaultService, $exception->getMessage());
            }
        }
    }

    /**
     * @test
     * @covers \Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension::load
     *
     * @uses   \Dziki\MonologSentryBundle\DependencyInjection\Configuration
     */
    public function checkIfNativeUserAgentParserServiceDefinedAndPrivate(): void
    {
        if (!ini_get('browscap')) {
            $this->markTestSkipped(
                'The browscap.ini directive not set, skipped.'
            );
        }

        $this->load(
            [
                'user_context' => false,
                'user_agent_parser' => 'native',
            ]
        );

        $defaultServices = [
            NativeParser::class => [
                'class' => NativeParser::class,
            ],
        ];
        $this->compile();

        $this->assertServicesDefinedAndPrivate($defaultServices);
    }

    /**
     * @test
     * @covers \Dziki\MonologSentryBundle\DependencyInjection\MonologSentryExtension::load
     *
     * @uses   \Dziki\MonologSentryBundle\DependencyInjection\Configuration
     */
    public function checkIfCachedParserServiceDefinedAndPrivate(): void
    {
        $this->load(
            [
                'user_context' => false,
                'user_agent_parser' => 'phpuseragent',
                'cache' => 'app.cache.simple',
            ]
        );

        $defaultServices = [
            PhpUserAgentParser::class => [
                'class' => PhpUserAgentParser::class,
            ],
            CachedParser::class => [
                'class' => CachedParser::class,
            ],
        ];

        $this->registerService('app.cache.simple', new ArrayCache());
        $this->compile();

        $this->assertServicesDefinedAndPrivate($defaultServices);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new MonologSentryExtension(),
        ];
    }
}
