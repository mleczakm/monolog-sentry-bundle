<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\NativeParser;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;

/**
 * @covers \Dziki\MonologSentryBundle\UserAgent\NativeParser
 *
 * @uses \Dziki\MonologSentryBundle\UserAgent\UserAgent
 */
class NativeParserTest extends AbstractParserTest
{
    public function validUserAgentsDataProvider(): array
    {
        return parent::validUserAgentsDataProvider() +
            [
                [
                    'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0',
                    UserAgent::create('Firefox', '47.0', 'Win7'),
                ],
                [
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0',
                    UserAgent::create('Firefox', '42.0', 'MacOSX'),
                ],
                [
                    'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
                    UserAgent::create('Safari', '11.0', 'iOS'),
                ],
                [
                    'Mozilla/5.0 (Linux; Android 7.0; SM-G892A Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/60.0.3112.107 Mobile Safari/537.36',
                    UserAgent::create('Android WebView', '60.0', 'Android'),
                ],
                [
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
                    UserAgent::create('Edge', '12.0', 'Win10'),
                ],
            ];
    }

    public function setUp(): void
    {
        if (!ini_get('browscap')) {
            $this->markTestSkipped(
                'The browscap.ini directive not set, skipped.'
            );
        }

        $this->setParser(new NativeParser());
    }
}
