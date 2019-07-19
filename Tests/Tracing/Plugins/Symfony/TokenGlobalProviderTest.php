<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\TokenGlobalProvider;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class TokenGlobalProviderTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    /** @var TokenGlobalProvider */
    private $tokenGlobalProvider;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->tokenGlobalProvider = new TokenGlobalProvider($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_initializes_global_token(): void
    {
        $this->tokenGlobalProvider->init();

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_creates_global_token(): void
    {
        $this->tokenGlobalProvider->setFromRequest(new Request());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_creates_global_token_with_value_from_request(): void
    {
        $request = new Request();
        $request->headers->set('x-token-global', 'some_token');

        $this->tokenGlobalProvider->setFromRequest($request);

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

        $this->tokenGlobalProvider->setToResponse($response);

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

        $this->tokenGlobalProvider->clear();

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }
}
