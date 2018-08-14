<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

class PhpUserAgentParser implements Parser
{
    public function parse(string $userAgent): UserAgent
    {
        [
            'browser' => $browserName,
            'version' => $browserVersion,
            'platform' => $platform,
        ]
            = \parse_user_agent($userAgent);

        return UserAgent::create((string)$browserName, (string)$browserVersion, (string)$platform);
    }
}
