<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tests\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\Assert;
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
    private $httpClientMock;

    /** @var TokenCollection|ObjectProphecy */
    private $tokenCollectionMock;

    /** @var HttpClientDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->prophesize(HttpClientInterface::class);
        $this->tokenCollectionMock = $this->prophesize(TokenCollection::class);
        $this->decorator = new HttpClientDecorator(
            $this->httpClientMock->reveal(),
            $this->tokenCollectionMock->reveal()
        );
    }

    public function testInjectsTokenGlobalInRequestHeaders(): void
    {
        $options = [];
        $expectedOptions = [
            'headers' => [
                'X-Token-Global' => 'token_global',
            ],
        ];

        $this->tokenCollectionMock
            ->getTokenValue('global')
            ->willReturn('token_global')
        ;

        $this->httpClientMock
            ->request('GET', 'example.com/api', $expectedOptions)
            ->shouldBeCalled()
        ;

        $this->decorator->request('GET', 'example.com/api', $options);
    }

    public function testForwardsResponse(): void
    {
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();

        $this->httpClientMock
            ->request('GET', 'example.com/api', Argument::type('array'))
            ->willReturn($expectedResponse)
        ;

        $response = $this->decorator->request('GET', 'example.com/api', []);

        Assert::assertSame($response, $expectedResponse);
    }

    public function testForwardsStreamResponse(): void
    {
        $expectedResponse = $this->prophesize(ResponseStreamInterface::class)->reveal();

        $this->httpClientMock
            ->stream([], 30)
            ->willReturn($expectedResponse)
        ;

        $response = $this->decorator->stream([], 30);

        Assert::assertSame($response, $expectedResponse);
    }
}
