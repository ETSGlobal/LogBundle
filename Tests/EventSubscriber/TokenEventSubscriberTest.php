<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\EventSubscriber\TokenEventSubscriber;

use ETSGlobal\LogBundle\EventSubscriber\TokenEventSubscriber;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class TokenEventSubscriberTest extends TestCase
{
    /** @var TokenEventSubscriber */
    private $subscriber;

    /** @var TokenCollection */
    private $tokenCollection;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->subscriber = new TokenEventSubscriber($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_sets_all_tokens_in_response_headers(): void
    {
        $response = new Response();
        /** @var ResponseEvent|ObjectProphecy<ResponseEvent> $event */
        $event = $this->prophesize(ResponseEvent::class);
        $event
            ->getResponse()
            ->willReturn($response)
        ;

        $this->tokenCollection->add('global', 'foo');
        $this->tokenCollection->add('process', 'bar');

        $this->subscriber->setTokensInResponseHeaders($event->reveal());

        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-token-global', $headers);
        $this->assertEquals('foo', $headers['x-token-global'][0]);
        $this->assertArrayHasKey('x-token-process', $headers);
        $this->assertEquals('bar', $headers['x-token-process'][0]);
    }

    /**
     * @test
     */
    public function it_initializes_global_token(): void
    {
        /** @var ConsoleCommandEvent|ObjectProphecy<ConsoleCommandEvent> $event */
        $event = $this->prophesize(ConsoleCommandEvent::class);
        $this->subscriber->initializeGlobalToken($event->reveal());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_clears_global_token(): void
    {
        $this->tokenCollection->add('global');

        $this->subscriber->clearGlobalToken(null);

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }

    /**
     * @test
     */
    public function it_creates_global_token(): void
    {
        $request = new Request();
        /** @var GetResponseEvent|ObjectProphecy<GetResponseEvent> $event */
        $event = $this->prophesize(GetResponseEvent::class);
        $event
            ->getRequest()
            ->willReturn($request)
        ;

        $this->subscriber->createGlobalToken($event->reveal());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }
    /**
     * @test
     */
    public function it_uses_global_token_from_request_headers(): void
    {
        $request = new Request();
        /** @var GetResponseEvent|ObjectProphecy<GetResponseEvent> $event */
        $event = $this->prophesize(GetResponseEvent::class);
        $event
            ->getRequest()
            ->willReturn($request)
        ;

        $request->headers->set('X-Token-Global', 'some_token');

        $this->subscriber->createGlobalToken($event->reveal());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertEquals('some_token', $globalTokenValue);
    }
}
