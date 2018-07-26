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
}
