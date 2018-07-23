<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

class NativeParser implements Parser
{
    public function parse(string $userAgent): UserAgent
    {
        [
            'browser' => $browserName,
            'version' => $browserVersion,
            'platform' => $platform,
        ]
            = get_browser($userAgent, true);

        return UserAgent::create($browserName, $browserVersion, $platform);
    }
}
