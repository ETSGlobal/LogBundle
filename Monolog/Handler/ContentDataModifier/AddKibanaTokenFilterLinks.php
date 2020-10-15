<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Tracing\TokenCollection;

/**
 * @internal
 */
final class AddKibanaTokenFilterLinks implements ContentDataModifierInterface
{
    // phpcs:disable Generic.Files.LineLength.TooLong
    private const KIBANA_URI_PATTERN = '#/?_g=(filters:!(),time:(from:now-24h,to:now))&_a=(columns:!(_source),filters:!((\'$state\':(store:appState),meta:(alias:!n,disabled:!f,key:%1$s,negate:!f,params:(query:%2$s),type:phrase),query:(match_phrase:(%1$s:%2$s)))),interval:auto,query:(language:kuery,query:\'\'),sort:!())';

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
                'url' => sprintf(
                    $this->kibanaUrl.self::KIBANA_URI_PATTERN,
                    $key,
                    urlencode($record['extra'][$key])
                ),
            ];
        }
    }
}
