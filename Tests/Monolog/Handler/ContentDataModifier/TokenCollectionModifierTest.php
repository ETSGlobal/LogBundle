<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\TokenCollectionModifier;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

final class TokenCollectionModifierTest extends TestCase
{
    /** @var ObjectProphecy<TokenCollection>|TokenCollection */
    private $tokenCollectionMock;

    /** @var TokenCollectionModifier */
    private $tokenCollectionModifier;

    protected function setUp(): void
    {
        $this->tokenCollectionMock = $this->prophesize(TokenCollection::class);

        $this->tokenCollectionModifier = new TokenCollectionModifier(
            'my_fake_url tokenName(%s) tokenValue(%s)',
            $this->tokenCollectionMock->reveal()
        );
    }

    /**
     * @test
     * @dataProvider modifyDataProvider
     */
    public function it_adds_tokens_to_contents(array $record, array $tokens, array $expectedContentData): void
    {
        $contentData = [];
        $this->tokenCollectionMock->getTokens()->willReturn($tokens)->shouldBeCalled();

        $this->tokenCollectionModifier->modify($contentData, $record);

        $this->assertEquals($expectedContentData, $contentData);
    }

    public function modifyDataProvider(): array
    {
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
                    'test' => 'token_value',
                    'test2' => 'token_value',
                ],
                [
                    'attachments' => [
                        [
                            'actions' => [
                                [
                                    'text' => 'token_test',
                                    'type' => 'button',
                                    'url' => 'my_fake_url tokenName(token_test) tokenValue(extra_token_test_value)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
