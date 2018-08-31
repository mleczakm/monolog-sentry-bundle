<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Functional;

use Dziki\MonologSentryBundle\UserAgent\ParserInterface;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;

class CustomParserStub implements ParserInterface
{
    public function parse(string $userAgent): UserAgent
    {
        return UserAgent::create('', '', '');
    }
}
