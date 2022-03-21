<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\AddKibanaTokenFilterLinks;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/** @internal */
final class AddKibanaTokenFilterLinksTest extends TestCase
{
    private TokenCollection $tokenCollection;

    private AddKibanaTokenFilterLinks $modifier;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();

        $this->modifier = new AddKibanaTokenFilterLinks(
            'https://kibana.example.com/app/discover',
            $this->tokenCollection,
        );
    }

    /** @dataProvider modifyDataProvider */
    public function testAddsTokensToContents(array $record, array $tokens, array $expectedContentData): void
    {
        $contentData = [];

        foreach ($tokens as $name => $value) {
            $this->tokenCollection->add($name, $value);
        }

        $this->modifier->modify($contentData, $record);

        $this->assertEquals($expectedContentData, $contentData);
    }

    public function modifyDataProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            [
                ['message' => 'my_fake_message'],
                [],
                [],
            ],
            [
                [
                    'message' => 'my_fake_message',
                    'extra' => [
                        'token_global' => 'global_5f885585dc39a',
                    ],
                ],
                [
                    'global' => 'global_5f885585dc39a',
                    'process' => 'process_5f886d54a8443',
                ],
                [
                    'attachments' => [
                        [
                            'actions' => [
                                [
                                    'text' => 'token_global',
                                    'type' => 'button',
                                    'url' => 'https://kibana.example.com/app/discover#/?_g=(filters:!(),time:(from:now-24h,to:now))&_a=(columns:!(_source),filters:!((\'$state\':(store:appState),meta:(alias:!n,disabled:!f,key:token_global,negate:!f,params:(query:global_5f885585dc39a),type:phrase),query:(match_phrase:(token_global:global_5f885585dc39a)))),interval:auto,query:(language:kuery,query:\'\'),sort:!())',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
