<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\ClassLineModifier;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClassLineModifierTest extends TestCase
{
    /** @var ClassLineModifier */
    private $classLineModifier;

    protected function setUp(): void
    {
        $this->classLineModifier = new ClassLineModifier();
    }

    /**
     * @test
     * @dataProvider modifyDataProvider
     */
    public function it_adds_class_line_to_attachements(array $record, array $expectedContentData): void
    {
        $contentData = [];

        $this->classLineModifier->modify($contentData, $record);

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
