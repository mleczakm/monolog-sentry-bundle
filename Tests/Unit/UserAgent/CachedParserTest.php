<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\CachedParser;
use Dziki\MonologSentryBundle\UserAgent\Parser;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CachedParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider userAgentsDataProvider
     * @param string $requestedUserAgent
     */
    public function notFoundEntriesCallsNextParserAndAddToCache(string $requestedUserAgent): void
    {
        $cacheInterface = $this->createMock(CacheInterface::class);
        $cacheInterface->method('get')
                       ->willReturn(null)
        ;

        $parsedUserAgent = UserAgent::create('', '', '');

        $cacheInterface
            ->expects($this->once())
            ->method('set')
            ->with(md5($requestedUserAgent), serialize($parsedUserAgent))
                       ->willReturn(null)
        ;

        $nextParser = $this->createMock(Parser::class);
        $nextParser->expects($this->once())
                   ->method('parse')
                   ->with($requestedUserAgent)
                   ->willReturn($parsedUserAgent)
        ;

        $cachedParser = new CachedParser($cacheInterface, $nextParser);

        $cachedParser->parse($requestedUserAgent);
    }

    public function userAgentsDataProvider(): array
    {
        return [
            ['a'],
            ['b'],
            ['c'],
            ['d'],
            ['e'],
        ];
    }

    /**
     * @test
     * @dataProvider userAgentsDataProvider
     * @param string $requestedUserAgent
     */
    public function foundEntriesReturnsItImmediately(string $requestedUserAgent): void
    {
        $parsedSerializedAgent = serialize(UserAgent::create('', '', ''));

        $cacheInterface = $this->createMock(CacheInterface::class);
        $cacheInterface->expects($this->once())
                       ->method('get')
                       ->with(md5($requestedUserAgent))
                       ->willReturn($parsedSerializedAgent)
        ;

        $nextParser = $this->createMock(Parser::class);

        $cachedParser = new CachedParser($cacheInterface, $nextParser);

        $cachedParser->parse($requestedUserAgent);
    }
}
