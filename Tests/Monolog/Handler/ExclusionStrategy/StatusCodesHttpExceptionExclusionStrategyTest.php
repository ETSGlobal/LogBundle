<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\StatusCodesHttpExceptionExclusionStrategy;
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
        $this->assertFalse($this->statusCodesHttpExceptionExclusionStrategy->excludeRecord([]));
    }

    public function testDoesNotExcludeRecordWhenInvalidException(): void
    {
        $this->assertFalse(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
                ['context' => ['exception' => new \stdClass()]],
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
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(['context' => ['exception' => $exception]]),
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
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(['context' => ['exception' => $exception]]),
        );
    }
}
