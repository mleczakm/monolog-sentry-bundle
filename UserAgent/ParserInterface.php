<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

interface ParserInterface
{
    public function parse(string $userAgent): UserAgent;
}
