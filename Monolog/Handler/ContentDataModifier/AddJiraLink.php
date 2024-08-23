<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

/** @internal */
final class AddJiraLink implements ContentDataModifierInterface
{
    private const string JIRA_PATH = '/secure/CreateIssue.jspa';

    private const array URI_PARAMS = [
        'pid=10631', // Project ID
        'issueType=1', // Bug
        'summary=%1$s', // Replaced by log message
        'description=%1$s', // Replaced by log message
    ];

    public function __construct(private readonly string $jiraUrl)
    {
    }

    public function modify(array &$contentData, array $record): void
    {
        if (!isset($record['message'])) {
            return;
        }

        if (!isset($contentData['attachments'][0]['actions'])) {
            $contentData['attachments'][0]['actions'] = [];
        }

        $query = implode('&', self::URI_PARAMS);
        $url = implode('?', [
            $this->jiraUrl . self::JIRA_PATH,
            sprintf($query, urlencode($record['message'])),
        ]);

        $contentData['attachments'][0]['actions'][] = [
            'text' => 'Create ticket',
            'type' => 'button',
            'style' => 'primary',
            'url' => $url,
        ];
    }
}
