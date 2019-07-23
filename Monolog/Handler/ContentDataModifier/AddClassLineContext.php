<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

/**
 * @internal
 */
final class AddClassLineContext implements ContentDataModifierInterface
{
    public function modify(array &$contentData, array $record): void
    {
        if (!isset($record['context']['class'])) {
            return;
        }

        $url = $record['context']['class'];
        if (isset($record['context']['line'])) {
            $url .= ':'.$record['context']['line'];
        }

        if (!isset($contentData['attachments'][0]['fields'])) {
            $contentData['attachments'][0]['fields'] = [];
        }

        $contentData['attachments'][0]['fields'][] = [
            'title' => 'Class',
            'value' => $url,
        ];
    }
}
