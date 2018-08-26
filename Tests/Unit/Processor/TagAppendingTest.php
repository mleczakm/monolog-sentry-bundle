<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\Processor;

use Dziki\MonologSentryBundle\Processor\TagAppending;
use PHPUnit\Framework\TestCase;

class TagAppendingTest extends TestCase
{
    /**
     * @dataProvider stringValuesWithIndexesDataProvider
     * @test
     *
     * @param string $index
     * @param string $tagValue
     */
    public function appendStringValueUnderSpecifiedIndex(string $index, string $tagValue): void
    {
        $tagAppendingProcessor = new TagAppending($index, $tagValue);

        $resultRecord = $tagAppendingProcessor([]);

        $tags = $resultRecord['extra']['tags'];

        $this->assertArrayHasKey($index, $tags);
        $this->assertEquals($tagValue, $tags[$index]);
    }

    public function stringValuesWithIndexesDataProvider(): array
    {
        return [
            ['1', 'one'],
            ['', 'one'],
            ['', ''],
        ];
    }
}
