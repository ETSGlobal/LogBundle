<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpKernelToken;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class HttpKernelTokenTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    /** @var HttpKernelToken */
    private $httpKernelToken;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->httpKernelToken = new HttpKernelToken($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_creates_token(): void
    {
        $this->httpKernelToken->setFromRequest(new Request());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_creates_token_with_value_from_request(): void
    {
        $request = new Request();
        $request->headers->set('X-Token-Global', 'some_token');

        $this->httpKernelToken->setFromRequest($request);

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertEquals('some_token', $globalTokenValue);
    }

    /**
     * @test
     */
    public function it_sets_tokens_to_response(): void
    {
        $response = new Response();

        $this->tokenCollection->add('global', 'foo');
        $this->tokenCollection->add('process', 'bar');

        $this->httpKernelToken->setToResponse($response);

        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-token-global', $headers);
        $this->assertEquals('foo', $headers['x-token-global'][0]);
        $this->assertArrayHasKey('x-token-process', $headers);
        $this->assertEquals('bar', $headers['x-token-process'][0]);
    }

    /**
     * @test
     */
    public function it_clears_global_token(): void
    {
        $this->tokenCollection->add('global');

        $this->httpKernelToken->clear();

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }
}
