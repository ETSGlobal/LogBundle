<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\AddClassLineContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddClassLineContextTest extends TestCase
{
    /** @var AddClassLineContext */
    private $modifier;

    protected function setUp(): void
    {
        $this->modifier = new AddClassLineContext();
    }

    /**
     * @test
     * @dataProvider modifyDataProvider
     */
    public function it_adds_class_line_to_attachements(array $record, array $expectedContentData): void
    {
        $contentData = [];

        $this->modifier->modify($contentData, $record);

        $this->assertEquals($expectedContentData, $contentData);
    }

    public function modifyDataProvider(): array
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
