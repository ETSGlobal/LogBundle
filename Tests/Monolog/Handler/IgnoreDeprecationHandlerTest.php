<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler;

use ETSGlobal\LogBundle\Monolog\Handler\IgnoreDeprecationHandler;
use PHPUnit\Framework\TestCase;

class IgnoreDeprecationHandlerTest extends TestCase
{
    public function provideData(): array
    {
        return [
            [
                [
                    'channel' => 'php',
                    'message' => 'User Deprecated: ',
                ],
                true,
            ],
            [
                [
                    'channel' => 'something else',
                    'message' => 'User Deprecated: ',
                ],
                false,
            ],
            [
                [
                    'channel' => 'php',
                    'message' => 'great log',
                ],
                false,
            ],
        ];
    }

    /** @dataProvider provideData */
    public function testHandle(array $record, bool $expected): void
    {
        $handler = new IgnoreDeprecationHandler();
        $this->assertSame($expected, $handler->handle($record));
    }
}
