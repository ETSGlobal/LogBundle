<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\EventSubscriber\TracingEventSubscriber;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\Console;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpKernel;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * @internal
 */
final class TracingEventSubscriberTest extends TestCase
{
    /** @var TracingEventSubscriber */
    private $subscriber;

    /** @var Console|ObjectProphecy<Console> */
    private $consoleMock;

    /** @var HttpKernel|ObjectProphecy<HttpKernel> */
    private $httpKernelMock;

    protected function setUp(): void
    {
        $this->consoleMock = $this->prophesize(Console::class);
        $this->httpKernelMock = $this->prophesize(HttpKernel::class);
        $this->subscriber = new TracingEventSubscriber(
            $this->consoleMock->reveal(),
            $this->httpKernelMock->reveal()
        );
    }

    /**
     * @test
     */
    public function it_sets_response_headers_on_kernel_response(): void
    {
        $response = new Response();
        /** @var FilterResponseEvent|ObjectProphecy<FilterResponseEvent> $event */
        $event = $this->prophesize(FilterResponseEvent::class);
        $event
            ->getResponse()
            ->willReturn($response)
        ;

        $this->httpKernelMock
            ->setToResponse($response)
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelResponse($event->reveal());
    }

    /**
     * @test
     */
    public function it_creates_token_on_console_command(): void
    {
        /** @var ConsoleCommandEvent|ObjectProphecy<ConsoleCommandEvent> $event */
        $event = $this->prophesize(ConsoleCommandEvent::class);

        $this->consoleMock
            ->create()
            ->shouldBeCalled()
        ;

        $this->subscriber->onConsoleCommand($event->reveal());
    }

    /**
     * @test
     */
    public function it_clears_token_on_kernel_terminate(): void
    {
        /** @var ObjectProphecy<PostResponseEvent>|PostResponseEvent $event */
        $event = $this->prophesize(PostResponseEvent::class);

        $this->httpKernelMock
            ->clear()
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelTerminate($event->reveal());
    }

    /**
     * @test
     */
    public function it_clears_token_on_console_terminate(): void
    {
        /** @var ConsoleTerminateEvent|ObjectProphecy<ConsoleTerminateEvent> $event */
        $event = $this->prophesize(ConsoleTerminateEvent::class);

        $this->consoleMock
            ->clear()
            ->shouldBeCalled()
        ;

        $this->subscriber->onConsoleTerminate($event->reveal());
    }

    /**
     * @test
     */
    public function it_creates_global_token_on_kernel_request(): void
    {
        $request = new Request();
        /** @var GetResponseEvent|ObjectProphecy<GetResponseEvent> $event */
        $event = $this->prophesize(GetResponseEvent::class);
        $event
            ->getRequest()
            ->willReturn($request)
        ;

        $this->httpKernelMock
            ->setFromRequest($request)
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelRequest($event->reveal());
    }
}
