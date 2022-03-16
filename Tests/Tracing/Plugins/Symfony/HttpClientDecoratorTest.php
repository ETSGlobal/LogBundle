<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tests\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class HttpClientDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /** @var HttpClientInterface|ObjectProphecy */
    private ObjectProphecy $httpClient;

    /** @var TokenCollection|ObjectProphecy */
    private ObjectProphecy $tokenCollection;

    private HttpClientDecorator $decorator;

    protected function setUp(): void
    {
        $this->httpClient = $this->prophesize(HttpClientInterface::class);
        $this->tokenCollection = $this->prophesize(TokenCollection::class);
        $this->decorator = new HttpClientDecorator($this->httpClient->reveal(), $this->tokenCollection->reveal());
    }

    public function testInjectsTokenGlobalInRequestHeaders(): void
    {
        $options = [];
        $expectedOptions = [
            'headers' => [
                'X-Token-Global' => 'token_global',
            ],
        ];

        $this->tokenCollection->getTokenValue('global')->shouldBeCalled()->willReturn('token_global');

        $this->httpClient->request('GET', 'example.com/api', $expectedOptions)->shouldBeCalled();

        $this->decorator->request('GET', 'example.com/api', $options);
    }

    public function testForwardsResponse(): void
    {
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();

        $this->httpClient
            ->request('GET', 'example.com/api', Argument::type('array'))
            ->willReturn($expectedResponse)
        ;

        $response = $this->decorator->request('GET', 'example.com/api', []);

        $this->assertSame($response, $expectedResponse);
    }

    public function testForwardsStreamResponse(): void
    {
        $expectedResponse = $this->prophesize(ResponseStreamInterface::class)->reveal();

        $this->httpClient->stream([], 30)->shouldBeCalled()->willReturn($expectedResponse);

        $response = $this->decorator->stream([], 30);

        $this->assertSame($response, $expectedResponse);
    }
}
