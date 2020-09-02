<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\StatusCodesHttpExceptionExclusionStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class StatusCodesHttpExceptionExclusionStrategyTest extends TestCase
{
    /** @var StatusCodesHttpExceptionExclusionStrategy */
    private $statusCodesHttpExceptionExclusionStrategy;

    protected function setUp(): void
    {
        $this->statusCodesHttpExceptionExclusionStrategy = new StatusCodesHttpExceptionExclusionStrategy([
            400,
            404,
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_exclude_record_when_no_exception(): void
    {
        $this->assertFalse($this->statusCodesHttpExceptionExclusionStrategy->excludeRecord([]));
    }

    /**
     * @test
     */
    public function it_does_not_exclude_record_when_invalid_exception(): void
    {
        $this->assertFalse(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(
                ['context' => ['exception' => new \stdClass()]]
            )
        );
    }

    public function exceptionProvider(): array
    {
        return [
            [new ConflictHttpException()],
            [new GoneHttpException()],
        ];
    }

    /**
     * @test
     * @dataProvider exceptionProvider
     */
    public function it_does_not_exclude_records_when_status_codes_not_excluded(\Throwable $exception): void
    {
        $this->assertFalse(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(['context' => ['exception' => $exception]])
        );
    }

    public function excludedExceptionProvider(): array
    {
        return [
            [new BadRequestHttpException()],
            [new NotFoundHttpException()],
        ];
    }

    /**
     * @test
     * @dataProvider excludedExceptionProvider
     */
    public function it_excludes_records_when_excluded_exception_codes(\Throwable $exception): void
    {
        $this->assertTrue(
            $this->statusCodesHttpExceptionExclusionStrategy->excludeRecord(['context' => ['exception' => $exception]])
        );
    }
}
