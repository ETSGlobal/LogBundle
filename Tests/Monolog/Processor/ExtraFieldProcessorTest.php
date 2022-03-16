<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\ExtraFieldProcessor;
use PHPUnit\Framework\TestCase;

/** @internal */
final class ExtraFieldProcessorTest extends TestCase
{
    public function testProcessRecord(): void
    {
        $processor = new ExtraFieldProcessor('foo', 'bar');

        $record = [
            'extra' => [],
        ];

        $newRecord = $processor->__invoke($record);

        $this->assertArrayHasKey('foo', $newRecord['extra']);
        $this->assertEquals('bar', $newRecord['extra']['foo']);
    }
}
