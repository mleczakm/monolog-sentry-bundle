<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\MonologSentryBundle;
use Dziki\MonologSentryBundle\Processor\TagAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\CachedParser;
use Dziki\MonologSentryBundle\UserAgent\NativeParser;
use Dziki\MonologSentryBundle\UserAgent\ParserInterface;
use Dziki\MonologSentryBundle\UserAgent\PhpUserAgentParser;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use Nyholm\BundleTest\AppKernel;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BundleInitializationTest extends BaseBundleTestCase
{
    /**
     * @test
     */
    public function checkPhpUserAgentParserLoaded(): void
    {
        $kernel = $this->prepareKernel();

        $this->checkDefaultServices($kernel);

        $phpUserAgentParser = $kernel->getContainer()->get(PhpUserAgentParser::class);
        $this->assertInstanceOf(PhpUserAgentParser::class, $phpUserAgentParser);
    }

    /**
     * @param string $configFilePath
     *
     * @return AppKernel
     */
    private function prepareKernel(string $configFilePath = 'config.yaml'): AppKernel
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        $kernel->addBundle(SecurityBundle::class);
        $kernel->addBundle(MonologBundle::class);

        $kernel->addConfigFile(__DIR__ . DIRECTORY_SEPARATOR . $configFilePath);

        // Make all services public
        $kernel->addCompilerPasses([new PublicServicePass()]);

        // Boot the kernel.
        $kernel->boot();

        return $kernel;
    }

    private function checkDefaultServices(AppKernel $kernel): void
    {
        $container = $this->getContainer();

        $this->logIn($container);

        $userDataAppending = $container->get('dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor');
        $this->assertInstanceOf(UserDataAppending::class, $userDataAppending);

        foreach (['symfony_version', 'commit', 'environment'] as $tagName) {
            $tagService = $container->get("dziki.monolog_sentry_bundle.{$tagName}_appending_processor");
            $this->assertInstanceOf(TagAppending::class, $tagService);
        }

        /** @var LoggerInterface $logger */
        $logger = $container->get('logger');

        $logger->info('some log');

        $kernel->handle(new Request());

        $logger->error('and now everything should be covered :)');
    }

    private function logIn(ContainerInterface $container): void
    {
        $session = $container->get('session');

        $firewallName = 'main';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = $firewallName;

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken('test', 'test', $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();
    }

    /**
     * @test
     */
    public function checkCacheServiceLoaded(): void
    {
        $kernel = $this->prepareKernel('config_with_cache.yaml');

        $cachedParser = $kernel->getContainer()->get(CachedParser::class);
        $this->assertInstanceOf(CachedParser::class, $cachedParser);
    }

    /**
     * @test
     */
    public function checkCustomParserLoadedWhenServiceNameProvided(): void
    {
        $kernel = $this->prepareKernel('config_with_custom_parser.yaml');

        $processorWithCustomParser = $kernel->getContainer()->get('dziki.monolog_sentry_bundle.browser_data_appending_subscribed_processor');
        $this->assertInstanceOf(BrowserDataAppending::class, $processorWithCustomParser);
    }

    /**
     * @test
     */
    public function checkNativeParserLoaded(): void
    {
        if (!ini_get('browscap')) {
            $this->markTestSkipped(
                'The browscap.ini directive not set, skipped.'
            );
        }

        $kernel = $this->prepareKernel('config_with_native_parser.yaml');

        $nativeParser = $kernel->getContainer()->get(NativeParser::class);
        $this->assertInstanceOf(NativeParser::class, $nativeParser);
    }

    protected function getBundleClass(): string
    {
        return MonologSentryBundle::class;
    }
}
