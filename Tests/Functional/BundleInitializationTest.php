<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\MonologSentryBundle;
use Dziki\MonologSentryBundle\Processor\TagAppending;
use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\CachedParser;
use Dziki\MonologSentryBundle\UserAgent\PhpUserAgentParser;
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
    public function checkDefaultServicesLoaded(): void
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        $kernel->addBundle(SecurityBundle::class);
        $kernel->addBundle(MonologBundle::class);

        $kernel->addConfigFile(__DIR__ . '/config.yaml');

        // Make all services public
        $kernel->addCompilerPasses([new PublicServicePass()]);

        // Boot the kernel.
        $kernel->boot();

        // Get the container
        $container = $this->getContainer();

        $this->logIn($container);

        $userDataAppending = $container->get('dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor');
        $this->assertInstanceOf(UserDataAppending::class, $userDataAppending);

        $cachedParser = $container->get(CachedParser::class);
        $this->assertInstanceOf(CachedParser::class, $cachedParser);

        $phpUserAgentParser = $container->get(PhpUserAgentParser::class);
        $this->assertInstanceOf(PhpUserAgentParser::class, $phpUserAgentParser);

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

    protected function getBundleClass(): string
    {
        return MonologSentryBundle::class;
    }
}
