<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Monolog\Handler;

use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\ExclusionStrategyInterface;
use ETSGlobal\LogBundle\Monolog\Handler\SlackHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SlackHandlerTest extends TestCase
{
    /** @var SlackHandler */
    private $slackHandler;

    protected function setUp(): void
    {
        $this->slackHandler = new SlackHandler('', '');
    }

    /**
     * @test
     */
    public function is_not_handling(): void
    {
        $fakeRecord = [];
        $exclusionStrategy = $this->prophesize(ExclusionStrategyInterface::class);
        $exclusionStrategy
            ->excludeRecord($fakeRecord)
            ->willReturn(true)
            ->shouldBeCalled()
        ;

        $this->slackHandler->addExclusionStrategy($exclusionStrategy->reveal());

        $this->assertFalse($this->slackHandler->isHandling($fakeRecord));
    }

    /**
     * @test
     */
    public function is_handling(): void
    {
        $fakeRecord = ['level' => Logger::CRITICAL];
        $exclusionStrategy = $this->prophesize(ExclusionStrategyInterface::class);
        $exclusionStrategy
            ->excludeRecord($fakeRecord)
            ->willReturn(false)
            ->shouldBeCalled()
        ;

        $this->slackHandler->addExclusionStrategy($exclusionStrategy->reveal());

        $this->assertTrue($this->slackHandler->isHandling($fakeRecord));
    }
}
