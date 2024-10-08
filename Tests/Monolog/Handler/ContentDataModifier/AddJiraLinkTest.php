<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\AddJiraLink;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
final class AddJiraLinkTest extends TestCase
{
    private AddJiraLink $modifier;

    protected function setUp(): void
    {
        $this->modifier = new AddJiraLink('https://example.jira.com');
    }

    #[DataProvider('modifyDataProvider')]
    public function testAddsJiraLink(array $record, array $expectedContentData): void
    {
        $contentData = [];

        $this->modifier->modify($contentData, $record);

        $this->assertEquals($expectedContentData, $contentData);
    }

    public static function modifyDataProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
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
                                    'url' => 'https://example.jira.com/secure/CreateIssue.jspa?pid=10631&issueType=1&summary=my_fake_message&description=my_fake_message',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
