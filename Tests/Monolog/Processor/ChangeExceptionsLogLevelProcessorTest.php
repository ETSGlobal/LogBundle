<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\ChangeExceptionsLogLevelProcessor;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChangeExceptionsLogLevelProcessorTest extends TestCase
{
    private ChangeExceptionsLogLevelProcessor $processor;

    protected function setUp(): void
    {
        $customExceptions = [
            \RuntimeException::class => Logger::ERROR,
            'Some\\Unexisting\\Exception' => Logger::INFO,
        ];

        $httpEceptions = [
            NotFoundHttpException::class => Logger::WARNING,
            AccessDeniedHttpException::class => Logger::ERROR,
        ];

        $this->processor = new ChangeExceptionsLogLevelProcessor($customExceptions, $httpEceptions);
    }

    public function testChangesLogLevelOnCustomException(): void
    {
        $exception = new \RuntimeException();
        $record = [
            'context' => [
                'exception' => $exception,
            ],
            'level' => Logger::CRITICAL,
            'level_name' => 'CRITICAL',
        ];

        $expected = [
            'context' => [
                'exception' => $exception,
            ],
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
        ];

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals($expected, $result);
    }

    public function testChangesLogLevelOnHttpException(): void
    {
        $exception = new NotFoundHttpException();
        $record = [
            'context' => [
                'exception' => $exception,
            ],
            'level' => Logger::CRITICAL,
            'level_name' => 'CRITICAL',
        ];

        $expected = [
            'context' => [
                'exception' => $exception,
            ],
            'level' => Logger::WARNING,
            'level_name' => 'WARNING',
        ];

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals($expected, $result);
    }

    public function testDoesNothingWhenNoException(): void
    {
        $record = [
            'context' => [],
            'level' => Logger::INFO,
            'level_name' => 'INFO',
        ];

        $expected = [
            'context' => [],
            'level' => Logger::INFO,
            'level_name' => 'INFO',
        ];

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals($expected, $result);
    }

    public function testDoesNothingWhenNotInstanceOfException(): void
    {
        $record = [
            'context' => [
                'exception' => 'not an exception',
            ],
            'level' => Logger::INFO,
            'level_name' => 'INFO',
        ];

        $expected = [
            'context' => [
                'exception' => 'not an exception',
            ],
            'level' => Logger::INFO,
            'level_name' => 'INFO',
        ];

        $processor = $this->processor;
        $result = $processor($record);

        $this->assertEquals($expected, $result);
    }
}
