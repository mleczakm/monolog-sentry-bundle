<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\SubscribedProcessor;

use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class UserDataAppendingTest extends TestCase
{
    /**
     * @test
     */
    public function isSubscribedToKernelRequest()
    {
        $subscribedEvents = UserDataAppending::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertSame('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
