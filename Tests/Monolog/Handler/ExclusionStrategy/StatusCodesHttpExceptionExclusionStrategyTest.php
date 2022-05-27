<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\StatusCodesHttpExceptionExclusionStrategy;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @internal */
final class StatusCodesHttpExceptionExclusionStrategyTest extends TestCase
{
    private StatusCodesHttpExceptionExclusionStrategy $statusCodesHttpExceptionExclusionStrategy;

    protected function setUp(): void
    {
        $this->statusCodesHttpExceptionExclusionStrategy = new StatusCodesHttpExceptionExclusionStrategy([
            400,
            404,
        ]);
    }

    public function testDoesNotExcludeRecordWhenNoException(): void
    {
        $this->assertFalse($this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
            new LogRecord(
                new \DateTimeImmutable(),
                'php',
                Logger::toMonologLevel(100),
                'great log',
                ['exception' => new \stdClass()],
                ['token_tokenA' => 'tokenA_fake_value'],
            ),
        ));
    }

    public function testDoesNotExcludeRecordWhenInvalidException(): void
    {
        $this->assertFalse(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'php',
                    Logger::toMonologLevel(100),
                    'great log',
                    ['exception' => new \stdClass()],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
            ),
        );
    }

    public function exceptionProvider(): array
    {
        return [
            [new ConflictHttpException()],
            [new GoneHttpException()],
        ];
    }

    /** @dataProvider exceptionProvider */
    public function testDoesNotExcludeRecordsWhenStatusCodesNotExcluded(\Throwable $exception): void
    {
        $this->assertFalse(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'php',
                    Logger::toMonologLevel(100),
                    'great log',
                    ['exception' => $exception],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
            ),
        );
    }

    public function excludedExceptionProvider(): array
    {
        return [
            [new BadRequestHttpException()],
            [new NotFoundHttpException()],
        ];
    }

    /** @dataProvider excludedExceptionProvider */
    public function testExcludesRecordsWhenExcludedExceptionCodes(\Throwable $exception): void
    {
        $this->assertTrue(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'php',
                    Logger::toMonologLevel(100),
                    'great log',
                    ['exception' => $exception],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
            ),
        );
    }
}
