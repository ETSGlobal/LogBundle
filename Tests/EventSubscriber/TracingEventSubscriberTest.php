<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\EventSubscriber\TracingEventSubscriber;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpFoundation;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
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

    /** @var TokenCollection */
    private $tokenCollection;

    /** @var HttpFoundation|ObjectProphecy<HttpFoundation> */
    private $httpFoundation;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->httpFoundation = $this->prophesize(HttpFoundation::class);
        $this->subscriber = new TracingEventSubscriber(
            $this->tokenCollection,
            $this->httpFoundation->reveal()
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

        $this->httpFoundation
            ->setToResponse($response)
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelResponse($event->reveal());
    }

    /**
     * @test
     */
    public function it_initializes_token_on_console_command(): void
    {
        /** @var ConsoleCommandEvent|ObjectProphecy<ConsoleCommandEvent> $event */
        $event = $this->prophesize(ConsoleCommandEvent::class);

        $this->subscriber->onConsoleCommand($event->reveal());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_clears_token_on_kernel_terminate(): void
    {
        /** @var ObjectProphecy<PostResponseEvent>|PostResponseEvent $event */
        $event = $this->prophesize(PostResponseEvent::class);

        $this->tokenCollection->add('global');

        $this->subscriber->onKernelTerminate($event->reveal());

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    /**
     * @test
     */
    public function it_clears_token_on_console_terminate(): void
    {
        /** @var ConsoleTerminateEvent|ObjectProphecy<ConsoleTerminateEvent> $event */
        $event = $this->prophesize(ConsoleTerminateEvent::class);

        $this->tokenCollection->add('global');

        $this->subscriber->onConsoleTerminate($event->reveal());

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
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

        $this->httpFoundation
            ->setFromRequest($request)
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelRequest($event->reveal());
    }
}
