<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Formatter;

use ETSGlobal\LogBundle\Monolog\Formatter\TokenCollectionFormatter;
use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/** @internal */
final class TokenCollectionFormatterTest extends TestCase
{
    use ProphecyTrait;

    /** @var TokenCollection|ObjectProphecy */
    private ObjectProphecy $tokenCollection;

    private TokenCollectionFormatter $tokenCollectionFormatter;

    protected function setUp(): void
    {
        $this->tokenCollection = $this->prophesize(TokenCollection::class);

        $this->tokenCollectionFormatter = new TokenCollectionFormatter(
            $this->tokenCollection->reveal(),
            "[%%token_collection%%]\n",
        );
    }

    public function testFormatWithoutToken(): void
    {
        $this->tokenCollection->getTokens()->shouldBeCalled()->willReturn([]);

        $this->assertEquals("[%%]\n", $this->tokenCollectionFormatter->format([
            'extra' => [],
            'context' => [],
        ]));
    }

    public function testFormatWithToken(): void
    {
        $this->tokenCollection->getTokens()->shouldBeCalled()->willReturn([
            'tokenA' => new Token('tokenA', 'tokenA_fake_value'),
        ]);

        $this->assertEquals("[%tokenA_fake_value%]\n", $this->tokenCollectionFormatter->format([
            'extra' => ['token_tokenA' => 'tokenA_fake_value'],
            'context' => [],
        ]));
    }
}
