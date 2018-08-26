<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
    /**
     * @test
     * @dataProvider parsedUserAgentDataProvider
     *
     * @param string $browser
     * @param string $version
     * @param string $platform
     */
    public function createsThemselvesAndProvideBrowserNameVersionAndPlatform(string $browser, string $version, string $platform): void
    {
        $userAgent = UserAgent::create($browser, $version, $platform);

        $this->assertSame($platform, $userAgent->getPlatform());
        $this->assertSame($version, $userAgent->getBrowserVersion());
        $this->assertSame($browser, $userAgent->getBrowseName());
    }

    public function parsedUserAgentDataProvider(): array
    {
        return [
            ['mozilla', '0.1', 'Mac OS'],
            ['', '', ''],
            ['not a browser', 'magic one', 'raspberry'],
        ];
    }
}
