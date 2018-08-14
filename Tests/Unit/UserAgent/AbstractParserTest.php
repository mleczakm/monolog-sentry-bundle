<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\Parser;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use PHPUnit\Framework\TestCase;

abstract class AbstractParserTest extends TestCase
{
    /** @var Parser */
    private $parser;

    public function validUserAgentsDataProvider(): array
    {
        return [
            [
                'Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0',
                UserAgent::create('Firefox', '62.0', 'Linux'),
            ],
        ];
    }

    public function invalidUserAgentsDataProvider(): array
    {
        return [
            [''],
            ['Googlebot/2.1 (+http://www.google.com/bot.html)'],
            ['AppleTV5,3/9.1.1'],
            ['Googlebot'],
            ['Google (+https://developers.google.com/+/web/snippet/)'],
            ['Bingbot'],
            ['Mozilla/5.0 (compatible; Bingbot/2.0; +http://www.bing.com/bingbot.htm)'],
            ['Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)'],
        ];
    }

    protected function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * @test
     * @dataProvider validUserAgentsDataProvider
     *
     * @param string $userAgent
     * @param UserAgent $expectedUserAgent
     */
    public function parseValidUserAgent(string $userAgent, UserAgent $expectedUserAgent): void
    {
        $resultedUserAgent = $this->parser->parse($userAgent);

        $this->assertEquals($expectedUserAgent, $resultedUserAgent);
    }

    /**
     * @test
     * @dataProvider invalidUserAgentsDataProvider
     * @doesNotPerformAssertions
     *
     * @param string $userAgent
     */
    public function doNotBreakOnInvalidUserAgents(string $userAgent): void
    {
        try {
            $this->parser->parse($userAgent);
        } catch (\Throwable $exception) {
            $this->fail();
        }
    }
}
