<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\ChangeExceptionsLogLevelProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChangeExceptionsLogLevelProcessorTest extends TestCase
{
    private ChangeExceptionsLogLevelProcessor $processor;

    protected function setUp(): void
    {
        $customExceptions = [
            \RuntimeException::class => Level::Error->value,
            'Some\\Unexisting\\Exception' => Level::Info->value,
        ];

        $httpEceptions = [
            NotFoundHttpException::class => Level::Warning->value,
            AccessDeniedHttpException::class => Level::Error->value,
        ];

        $this->processor = new ChangeExceptionsLogLevelProcessor($customExceptions, $httpEceptions);
    }

    public function testChangesLogLevelOnCustomException(): void
    {
        $exception = new \RuntimeException();
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Critical,
            'great log',
            ['exception' => $exception],
        );

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals(['exception' => $exception], $result['context']);
        $this->assertEquals(Level::Error->value, $result['level']);
    }

    public function testChangesLogLevelOnHttpException(): void
    {
        $exception = new NotFoundHttpException();

        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Critical,
            'great log',
            ['exception' => $exception],
        );

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals(['exception' => $exception], $result['context']);
        $this->assertEquals(Level::Warning->value, $result['level']);
    }

    public function testDoesNothingWhenNoException(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Info,
            'great log',
        );

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals(Level::Info->value, $result['level']);
    }

    public function testDoesNothingWhenNotInstanceOfException(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Info,
            'great log',
            ['exception' => 'not an exception'],
        );

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals(['exception' => 'not an exception'], $result['context']);
        $this->assertEquals(Level::Info->value, $result['level']);
    }
}
