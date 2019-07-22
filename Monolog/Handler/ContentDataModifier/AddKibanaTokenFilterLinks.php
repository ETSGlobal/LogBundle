<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Tracing\TokenCollection;

/**
 * @internal
 */
final class AddKibanaTokenFilterLinks implements ContentDataModifierInterface
{
    /** @var string */
    private $kibanaUrl;

    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(string $kibanaUrl, TokenCollection $tokenCollection)
    {
        $this->kibanaUrl = $kibanaUrl;
        $this->tokenCollection = $tokenCollection;
    }

    public function modify(array &$contentData, array $record): void
    {
        foreach (array_keys($this->tokenCollection->getTokens()) as $name) {
            if (!isset($contentData['attachments'][0]['actions'])) {
                $contentData['attachments'][0]['actions'] = [];
            }

            $key = 'token_'.$name;
            if (!isset($record['extra'][$key])) {
                continue;
            }

            $contentData['attachments'][0]['actions'][] = [
                'text' => $key,
                'type' => 'button',
                'url' => sprintf($this->kibanaUrl, $key, urlencode($record['extra'][$key])),
            ];
        }
    }
}
