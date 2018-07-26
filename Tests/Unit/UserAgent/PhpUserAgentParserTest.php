<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\PhpUserAgentParser;

class PhpUserAgentParserTest extends AbstractParserTest
{
    public function setUp()
    {
        if (!function_exists('parse_user_agent')) {
            $this->markTestSkipped(
                'Library "donatj/phpuseragentparser" not installed, skipped.'
            );
        }

        $this->setParser(new PhpUserAgentParser());
    }
}
