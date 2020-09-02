<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\EventSubscriber\TracingEventSubscriber;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpFoundation;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
final class TracingEventSubscriberTest extends TestCase
{
    use ProphecyTrait;

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
        /** @var FilterResponseEvent|ResponseEvent $event */
        $event = $this->createKernelEvent(ResponseEvent::class, FilterResponseEvent::class);

        $response = new Response();
        $event->setResponse($response);

        $this->httpFoundation
            ->setToResponse($response)
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelResponse($event);
    }

    /**
     * @test
     */
    public function it_initializes_token_on_console_command(): void
    {
        /** @var ConsoleCommandEvent $event */
        $event = $this->createConsoleEvent(ConsoleCommandEvent::class);

        $this->subscriber->onConsoleCommand($event);

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_clears_token_on_kernel_terminate(): void
    {
        /** @var ResponseEvent|PostResponseEvent $event */
        $event = $this->createKernelEvent(ResponseEvent::class, PostResponseEvent::class);

        $this->tokenCollection->add('global');

        $this->subscriber->onKernelTerminate($event);

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    /**
     * @test
     */
    public function it_clears_token_on_console_terminate(): void
    {
        /** @var ConsoleTerminateEvent $event */
        $event = $this->createConsoleEvent(ConsoleTerminateEvent::class);

        $this->tokenCollection->add('global');

        $this->subscriber->onConsoleTerminate($event);

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    /**
     * @test
     */
    public function it_creates_global_token_on_kernel_request(): void
    {
        /** @var RequestEvent|GetResponseEvent $event */
        $event = $this->createKernelEvent(RequestEvent::class, GetResponseEvent::class);

        $this->httpFoundation
            ->setFromRequest(Argument::type(Request::class))
            ->shouldBeCalled()
        ;

        $this->subscriber->onKernelRequest($event);
    }

    private function createKernelEvent($eventClass, $fallbackClass = null)
    {
        $eventClassToUse = class_exists($eventClass) ? $eventClass : $fallbackClass;
        if ($eventClassToUse === null) {
            throw new \RuntimeException(sprintf('Class %s doesn\'t exist.', $eventClass));
        }

        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $request = $this->prophesize(Request::class)->reveal();

        if ($eventClassToUse === ResponseEvent::class) {
            return new $eventClassToUse($kernel, $request, HttpKernelInterface::MASTER_REQUEST, new Response());
        }

        if ($eventClassToUse === FilterResponseEvent::class) {
            return new $eventClassToUse($kernel, $request, HttpKernelInterface::MASTER_REQUEST, new Response());
        }

        if ($eventClassToUse === PostResponseEvent::class) {
            return new $eventClassToUse($kernel, $request, new Response());
        }

        return new $eventClassToUse($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function createConsoleEvent($eventClass)
    {
        $command = new Command('test');
        $input = $this->prophesize(InputInterface::class)->reveal();
        $output = $this->prophesize(OutputInterface::class)->reveal();

        if ($eventClass === ConsoleTerminateEvent::class) {
            return new $eventClass($command, $input, $output, 0);
        }

        return new $eventClass($command, $input, $output);
    }
}
