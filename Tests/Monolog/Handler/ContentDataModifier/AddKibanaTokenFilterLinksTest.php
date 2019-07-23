<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\AddKibanaTokenFilterLinks;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddKibanaTokenFilterLinksTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    /** @var AddKibanaTokenFilterLinks */
    private $modifier;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();

        $this->modifier = new AddKibanaTokenFilterLinks(
            'https://kibana.example.com/app/kibana',
            $this->tokenCollection
        );
    }

    /**
     * @test
     * @dataProvider modifyDataProvider
     */
    public function it_adds_tokens_to_contents(array $record, array $tokens, array $expectedContentData): void
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
                        'token_test' => 'extra_token_test_value',
                    ],
                ],
                [
                    'test' => 'extra_token_test_value',
                    'test2' => 'token_value',
                ],
                [
                    'attachments' => [
                        [
                            'actions' => [
                                [
                                    'text' => 'token_test',
                                    'type' => 'button',
                                    'url' => 'https://kibana.example.com/app/kibana#/discover?_g=()&_a=(columns:!(_source),filters:!((\'$state\':(store:appState),meta:(alias:!n,disabled:!f,index:\'logstash-*\',key:token_test,negate:!f,value:extra_token_test_value),query:(match:(token_test:(query:extra_token_test_value,type:phrase))))),index:\'logstash-*\',interval:auto,query:\'\',sort:!(\'@timestamp\',desc))',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
