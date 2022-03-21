<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpFoundation;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @internal */
final class HttpFoundationTest extends TestCase
{
    private TokenCollection $tokenCollection;

    private HttpFoundation $httpFoundation;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->httpFoundation = new HttpFoundation($this->tokenCollection);
    }

    public function testCreatesGlobalToken(): void
    {
        $this->httpFoundation->setFromRequest(new Request());

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    public function testCreatesGlobalTokenWithValueFromRequest(): void
    {
        $request = new Request();
        $request->headers->set('x-token-global', 'some_token');

        $this->httpFoundation->setFromRequest($request);

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertEquals('some_token', $globalTokenValue);
    }

    public function testSetsTokensToResponse(): void
    {
        $response = new Response();

        $this->tokenCollection->add('global', 'foo');
        $this->tokenCollection->add('process', 'bar');

        $this->httpFoundation->setToResponse($response);

        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-token-global', $headers);
        $this->assertEquals('foo', $headers['x-token-global'][0]);
        $this->assertArrayHasKey('x-token-process', $headers);
        $this->assertEquals('bar', $headers['x-token-process'][0]);
    }
}
