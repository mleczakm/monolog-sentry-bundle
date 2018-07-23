<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

interface Parser
{
    public function parse(string $userAgent): UserAgent;
}
