<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Guzzle;

use ETSGlobal\LogBundle\Tracing\Plugins\Guzzle\TokenGlobalMiddleware;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class TokenGlobalMiddlewareTest extends TestCase
{
    /**
     * @var ObjectProphecy<RequestInterface>|RequestInterface
     */
    private $requestInterfaceMock;

    /**
     * @var TokenCollection
     */
    private $tokenCollection;

    /**
     * @var TokenGlobalMiddleware
     */
    private $tokenGlobalMiddleware;

    protected function setUp(): void
    {
        $this->requestInterfaceMock = $this->prophesize(RequestInterface::class);

        $this->tokenCollection = new TokenCollection();

        $this->tokenGlobalMiddleware = new TokenGlobalMiddleware($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_forwards_global_token(): void
    {
        $this->tokenCollection->add('global', 'token_value');

        $this->requestInterfaceMock
            ->withHeader('x-tokenGlobalProvider-global', 'token_value')
            ->willReturn($this->requestInterfaceMock->reveal())
            ->shouldBeCalled()
        ;

        $handler = \call_user_func($this->tokenGlobalMiddleware, static function ($request) {
            return $request;
        });

        $this->assertSame(
            $this->requestInterfaceMock->reveal(),
            $handler($this->requestInterfaceMock->reveal(), [])
        );
    }
}
