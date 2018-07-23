<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\UserAgent;

class UserAgent
{
    /**
     * @var string
     */
    private $browseName;
    /**
     * @var string
     */
    private $browserVersion;
    /**
     * @var string
     */
    private $platform;

    private function __construct(string $browseName, string $browserVersion, string $platform)
    {
        $this->browseName = $browseName;
        $this->browserVersion = $browserVersion;
        $this->platform = $platform;
    }

    public static function create(string $browseName, string $browserVersion, string $platform): self
    {
        return new self($browseName, $browserVersion, $platform);
    }

    public function getBrowseName(): string
    {
        return $this->browseName;
    }

    public function getBrowserVersion(): string
    {
        return $this->browserVersion;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }
}
