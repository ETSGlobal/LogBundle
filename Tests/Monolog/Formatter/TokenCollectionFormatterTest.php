<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Formatter;

use ETSGlobal\LogBundle\Monolog\Formatter\TokenCollectionFormatter;
use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @internal
 */
final class TokenCollectionFormatterTest extends TestCase
{
    /**
     * @var ObjectProphecy|TokenCollection
     */
    private $tokenCollectionMock;

    /**
     * @var TokenCollectionFormatter
     */
    private $tokenCollectionFormatter;

    protected function setUp(): void
    {
        $this->tokenCollectionMock = $this->prophesize(TokenCollection::class);

        $this->tokenCollectionFormatter = new TokenCollectionFormatter(
            $this->tokenCollectionMock->reveal(),
            "[%%token_collection%%]\n"
        );
    }

    /**
     * @test
     */
    public function format_without_token(): void
    {
        $this->tokenCollectionMock->getTokens()->willReturn([])->shouldBeCalled();

        $this->assertEquals("[%%]\n", $this->tokenCollectionFormatter->format([
            'extra' => [],
            'context' => [],
        ]));
    }

    /**
     * @test
     */
    public function format_with_token(): void
    {
        $this->tokenCollectionMock->getTokens()->willReturn([
            'tokenA' => new Token('tokenA', 'tokenA_fake_value'),
        ])->shouldBeCalled();

        $this->assertEquals("[%tokenA_fake_value%]\n", $this->tokenCollectionFormatter->format([
            'extra' => ['token_tokenA' => 'tokenA_fake_value'],
            'context' => [],
        ]));
    }
}
