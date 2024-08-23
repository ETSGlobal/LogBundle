<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\AddClassLineContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
final class AddClassLineContextTest extends TestCase
{
    private AddClassLineContext $modifier;

    protected function setUp(): void
    {
        $this->modifier = new AddClassLineContext();
    }

    #[DataProvider('modifyDataProvider')]
    public function testAddsClassLineToAttachements(array $record, array $expectedContentData): void
    {
        $contentData = [];

        $this->modifier->modify($contentData, $record);

        $this->assertEquals($expectedContentData, $contentData);
    }

    public static function modifyDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                ['context' => ['class' => 'my_fake_class']],
                [
                    'attachments' => [
                        ['fields' => [['title' => 'Class', 'value' => 'my_fake_class']]],
                    ],
                ],
            ],
            [
                ['context' => ['class' => 'my_fake_class', 'line' => 11]],
                [
                    'attachments' => [
                        ['fields' => [['title' => 'Class', 'value' => 'my_fake_class:11']]],
                    ],
                ],
            ],
        ];
    }
}
