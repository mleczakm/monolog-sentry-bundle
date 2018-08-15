<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\MonologSentryBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BundleInitializationTest extends BaseBundleTestCase
{
    public function testInitBundle()
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

        $this->assertNotNull($container->get('dziki.monolog_sentry_bundle.user_data_appending_subscribed_processor'));
    }

    private function logIn(Container $container)
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

    protected function getBundleClass()
    {
        return MonologSentryBundle::class;
    }
}