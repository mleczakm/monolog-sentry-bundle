<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Processor;

class TagAppending
{
    /**
     * @var string
     */
    private $tag;
    /**
     * @var string
     */
    private $value;

    public function __construct(string $tag, string $value)
    {
        $this->tag = $tag;
        $this->value = $value;
    }

    public function __invoke(array $record)
    {
        $record['extra']['tags'][$this->tag] = $this->value;

        return $record;
    }
}
