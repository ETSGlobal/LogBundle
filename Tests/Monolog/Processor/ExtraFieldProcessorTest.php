<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\ExtraFieldProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/** @internal */
final class ExtraFieldProcessorTest extends TestCase
{
    public function testProcessRecord(): void
    {
        $processor = new ExtraFieldProcessor('foo', 'bar');

        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Info,
            'great log',
        );

        $newRecord = $processor->__invoke($record);

        $this->assertArrayHasKey('foo', $newRecord['extra']);
        $this->assertEquals('bar', $newRecord['extra']['foo']);
    }
}
