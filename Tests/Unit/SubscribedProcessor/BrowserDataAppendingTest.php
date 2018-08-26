<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\SubscribedProcessor;

use Dziki\MonologSentryBundle\SubscribedProcessor\BrowserDataAppending;
use Dziki\MonologSentryBundle\UserAgent\ParserInterface;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BrowserDataAppendingTest extends TestCase
{
    /**
     * @var BrowserDataAppending
     */
    protected $browserDataAppendingProcessor;
    /**
     * @var ParserInterface|MockObject
     */
    protected $parser;

    /**
     * @test
     */
    public function isSubscribedToKernelRequest(): void
    {
        $subscribedEvents = BrowserDataAppending::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertSame(['onKernelRequest', 1024], $subscribedEvents[KernelEvents::REQUEST]);
    }

    /**
     * @test
     */
    public function parseUserAgentOnRequest(): BrowserDataAppending
    {
        /** @var ParserInterface|MockObject $parser */
        $parser = $this->createMock(ParserInterface::class);
        $this->parser = $parser;
        $browserDataAppendingProcessor = new BrowserDataAppending($parser);

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn(UserAgent::create('Firefox', '62.0', 'Linux'))
        ;

        $headersBag = new HeaderBag(
            ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0']
        );
        $request = $this->createMock(Request::class);
        $request->headers = $headersBag;
        /**
         * @var GetResponseEvent|MockObject
         */
        $event = $this->createMock(GetResponseEvent::class);
        $event->method('getRequest')
              ->willReturn($request)
        ;

        $browserDataAppendingProcessor->onKernelRequest($event);

        return $browserDataAppendingProcessor;
    }

    /**
     * @test
     * @depends parseUserAgentOnRequest
     *
     * @param BrowserDataAppending $browserDataAppendingProcessor
     */
    public function addParsedUserAgentDataToLogRecord(BrowserDataAppending $browserDataAppendingProcessor): void
    {
        $record = $browserDataAppendingProcessor([]);

        $this->assertArrayHasKey('contexts', $record);
        $this->assertArrayHasKey('browser', $record['contexts']);
        $this->assertArrayHasKey('name', $record['contexts']['browser']);
        $this->assertArrayHasKey('version', $record['contexts']['browser']);

        $this->assertArrayHasKey('os', $record['contexts']);
        $this->assertArrayHasKey('name', $record['contexts']['os']);
    }

    /**
     * @test
     */
    public function doNotAddAnythingIfUserAgentNotParsed(): void
    {
        $browserDataAppendingProcessor = new BrowserDataAppending($this->createMock(ParserInterface::class));

        $record = $browserDataAppendingProcessor([]);
        $this->assertSame([], $record);
    }
}
