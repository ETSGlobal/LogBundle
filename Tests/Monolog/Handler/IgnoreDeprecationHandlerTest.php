<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler;

use ETSGlobal\LogBundle\Monolog\Handler\IgnoreDeprecationHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IgnoreDeprecationHandlerTest extends TestCase
{
    public static function provideData(): array
    {
        return [
            [
                new LogRecord(
                    new \DateTimeImmutable(),
                    'php',
                    Logger::toMonologLevel(100),
                    'User Deprecated: ',
                    [],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
                true,
            ],
            [
                new LogRecord(
                    new \DateTimeImmutable(),
                    'something else',
                    Logger::toMonologLevel(100),
                    'User Deprecated: ',
                    [],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
                false,
            ],
            [
                new LogRecord(
                    new \DateTimeImmutable(),
                    'php',
                    Logger::toMonologLevel(100),
                    'great log',
                    [],
                    ['token_tokenA' => 'tokenA_fake_value'],
                ),
                false,
            ],
        ];
    }

    #[DataProvider('provideData')]
    public function testHandle(LogRecord $record, bool $expected): void
    {
        $handler = new IgnoreDeprecationHandler();
        $this->assertSame($expected, $handler->handle($record));
    }
}
