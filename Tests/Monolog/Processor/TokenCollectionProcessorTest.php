<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\TokenCollectionProcessor;
use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @internal
 */
final class TokenCollectionProcessorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|ObjectProphecy<TokenCollection>
     */
    private $tokenCollectionMock;

    /**
     * @var TokenCollectionProcessor
     */
    private $tokenProcessor;

    protected function setUp(): void
    {
        $this->tokenCollectionMock = $this->prophesize(TokenCollection::class);

        $this->tokenProcessor = new TokenCollectionProcessor($this->tokenCollectionMock->reveal());
    }

    /**
     * @test
     */
    public function process_record_without_prefix(): void
    {
        $tokenMock = $this->prophesize(Token::class);
        $tokenMock->getName()->willReturn('my_fake_token_name')->shouldBeCalled();
        $tokenMock->getValue()->willReturn('my_fake_token_value')->shouldBeCalled();
        $this->tokenCollectionMock->getTokens()->willReturn([$tokenMock->reveal()])->shouldBeCalled();

        $expectedRecord = [
            'extra' => [
                'token_my_fake_token_name' => 'my_fake_token_value',
            ],
        ];

        $this->assertEquals($expectedRecord, $this->tokenProcessor->__invoke([]));
    }
}
