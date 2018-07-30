<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

use Psr\SimpleCache\CacheInterface;

class CachedParser implements Parser
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(CacheInterface $cache, Parser $parser)
    {
        $this->cache = $cache;
        $this->parser = $parser;
    }

    public function parse(string $userAgent): UserAgent
    {
        $userAgentHash = md5($userAgent);

        if ($serializedUserAgent = $this->cache->get($userAgentHash)) {
            return unserialize($serializedUserAgent, ['allowed_classes' => [UserAgent::class]]);
        }

        $parsedUserAgent = $this->parser->parse($userAgent);
        $this->cache->set($userAgentHash, serialize($parsedUserAgent));

        return $parsedUserAgent;
    }
}
