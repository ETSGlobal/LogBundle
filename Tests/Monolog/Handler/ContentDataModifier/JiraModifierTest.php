<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\JiraModifier;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JiraModifierTest extends TestCase
{
    /** @var JiraModifier */
    private $jiraModifier;

    protected function setUp(): void
    {
        $this->jiraModifier = new JiraModifier('my_fake_url %s');
    }

    /**
     * @test
     * @dataProvider modifyDataProvider
     */
    public function it_adds_jira_link(array $record, array $expectedContentData): void
    {
        $contentData = [];

        $this->jiraModifier->modify($contentData, $record);

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
                ['message' => 'my_fake_message'],
                [
                    'attachments' => [
                        [
                            'actions' => [
                                [
                                    'text' => 'Create ticket',
                                    'type' => 'button',
                                    'style' => 'primary',
                                    'url' => 'my_fake_url my_fake_message',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
