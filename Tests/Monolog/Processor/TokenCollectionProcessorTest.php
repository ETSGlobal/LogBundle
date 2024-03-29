<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Monolog\Processor\TokenCollectionProcessor;
use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/** @internal */
final class TokenCollectionProcessorTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|TokenCollection */
    private ObjectProphecy $tokenCollectionMock;

    private TokenCollectionProcessor $tokenProcessor;

    protected function setUp(): void
    {
        $this->tokenCollectionMock = $this->prophesize(TokenCollection::class);

        $this->tokenProcessor = new TokenCollectionProcessor($this->tokenCollectionMock->reveal());
    }

    public function testProcessRecordWithoutPrefix(): void
    {
        $tokenMock = $this->prophesize(Token::class);
        $tokenMock->getName()->shouldBeCalled()->willReturn('my_fake_token_name');
        $tokenMock->getValue()->shouldBeCalled()->willReturn('my_fake_token_value');
        $this->tokenCollectionMock->getTokens()->shouldBeCalled()->willReturn([$tokenMock->reveal()]);

        $record = new LogRecord(
            new \DateTimeImmutable(),
            'php',
            Level::Info,
            'great log',
        );

        $result = $this->tokenProcessor->__invoke($record);

        $this->assertEquals(['token_my_fake_token_name' => 'my_fake_token_value'], $result['extra']);
    }
}
