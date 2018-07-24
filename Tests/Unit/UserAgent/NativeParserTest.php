<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\UserAgent;

use Dziki\MonologSentryBundle\UserAgent\NativeParser;

class NativeParserTest extends AbstractParserTest
{
    public function setUp()
    {
        if (!ini_get('browscap')) {
            $this->markTestSkipped(
                'The browscap.ini directive not set, skipped.'
            );
        }

        $this->setParser(new NativeParser());
    }
}