<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\SubscribedProcessor;

use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\Parser;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class BrowserDataAppendingTest extends TestCase
{
    /**
     * @var BrowserDataAppending
     */
    protected $browserDataAppendingProcessor;

    public function setUp()
    {
        /** @var Parser|MockObject $parser */
        $parser = $this->createMock(Parser::class);
        $parser->method('parse')
            ->willReturn(UserAgent::create('test', '1', 'Raspberry'));

        $this->browserDataAppendingProcessor = new BrowserDataAppending($parser);
    }

    /**
     * @test
     */
    public function isSubscribedToKernelRequest()
    {
        $subscribedEvents = BrowserDataAppending::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertSame('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
