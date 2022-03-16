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
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/** @internal */
final class TracingEventSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private TracingEventSubscriber $tracingEventSubscriber;

    private TokenCollection $tokenCollection;

    /** @var HttpFoundation|ObjectProphecy */
    private ObjectProphecy $httpFoundation;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->httpFoundation = $this->prophesize(HttpFoundation::class);
        $this->tracingEventSubscriber = new TracingEventSubscriber(
            $this->tokenCollection,
            $this->httpFoundation->reveal(),
        );
    }

    public function testSetsResponseHeadersOnKernelResponse(): void
    {
        $kernel = $this->prophesize(HttpKernelInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);
        $event = new ResponseEvent(
            $kernel->reveal(),
            $request->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->reveal(),
        );

        $this->httpFoundation->setToResponse($response)->shouldBeCalled();

        $this->tracingEventSubscriber->onKernelResponse($event);
    }

    public function testInitializesTokenOnConsoleCommand(): void
    {
        $command = new Command('test');
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $event = new ConsoleCommandEvent($command, $input->reveal(), $output->reveal());

        $this->tracingEventSubscriber->onConsoleCommand($event);

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    public function testClearsTokenOnKernelTerminate(): void
    {
        $kernel = $this->prophesize(HttpKernelInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);
        $event = new TerminateEvent($kernel->reveal(), $request->reveal(), $response->reveal());

        $this->tokenCollection->add('global');

        $this->tracingEventSubscriber->onKernelTerminate($event);

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    public function testClearsTokenOnConsoleTerminate(): void
    {
        $command = new Command('test');
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $event = new ConsoleTerminateEvent($command, $input->reveal(), $output->reveal(), Command::SUCCESS);

        $this->tokenCollection->add('global');

        $this->tracingEventSubscriber->onConsoleTerminate($event);

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    public function testCreatesGlobalTokenOnKernelRequest(): void
    {
        $kernel = $this->prophesize(HttpKernelInterface::class);
        $request = $this->prophesize(Request::class);
        $event = new RequestEvent($kernel->reveal(), $request->reveal(), HttpKernelInterface::MAIN_REQUEST);

        $this->httpFoundation->setFromRequest(Argument::type(Request::class))->shouldBeCalled();

        $this->tracingEventSubscriber->onKernelRequest($event);
    }
}
