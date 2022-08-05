<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler;

use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\ExclusionStrategyInterface;
use ETSGlobal\LogBundle\Monolog\Handler\SlackHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/** @internal */
final class SlackHandlerTest extends TestCase
{
    use ProphecyTrait;

    private SlackHandler $slackHandler;

    protected function setUp(): void
    {
        $this->slackHandler = new SlackHandler('', '');
    }

    public function testIsNotHandling(): void
    {
        $fakeRecord = new LogRecord(
            new \DateTimeImmutable(),
            'chan',
            Logger::toMonologLevel(100),
            'message',
            [],
            ['token_tokenA' => 'tokenA_fake_value'],
        );
        $exclusionStrategy = $this->prophesize(ExclusionStrategyInterface::class);
        $exclusionStrategy
            ->excludeRecord($fakeRecord)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->slackHandler->addExclusionStrategy($exclusionStrategy->reveal());

        $this->assertFalse($this->slackHandler->isHandling($fakeRecord));
    }

    public function testIsHandling(): void
    {
        $fakeRecord = new LogRecord(
            new \DateTimeImmutable(),
            'chan',
            Logger::toMonologLevel(Level::Critical),
            'message',
            [],
            ['token_tokenA' => 'tokenA_fake_value'],
        );

        $exclusionStrategy = $this->prophesize(ExclusionStrategyInterface::class);
        $exclusionStrategy
            ->excludeRecord($fakeRecord)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->slackHandler->addExclusionStrategy($exclusionStrategy->reveal());

        $this->assertTrue($this->slackHandler->isHandling($fakeRecord));
    }
}
