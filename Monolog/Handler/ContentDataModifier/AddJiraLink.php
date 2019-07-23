<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

/**
 * @internal
 */
final class AddJiraLink implements ContentDataModifierInterface
{
    /** @var string */
    private $jiraUrl;

    public function __construct(string $jiraUrl)
    {
        $this->jiraUrl = $jiraUrl;
    }

    public function modify(array &$contentData, array $record): void
    {
        if (!isset($record['message'])) {
            return;
        }

        if (!isset($contentData['attachments'][0]['actions'])) {
            $contentData['attachments'][0]['actions'] = [];
        }

        $contentData['attachments'][0]['actions'][] = [
            'text' => 'Create ticket',
            'type' => 'button',
            'style' => 'primary',
            'url' => sprintf($this->jiraUrl, urlencode($record['message'])),
        ];
    }
}
