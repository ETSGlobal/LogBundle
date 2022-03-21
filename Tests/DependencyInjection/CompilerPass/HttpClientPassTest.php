<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tests\DependencyInjection\CompilerPass;

use ETSGlobal\LogBundle\DependencyInjection\CompilerPass\HttpClientPass;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientPassTest extends TestCase
{
    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->addCompilerPass(new HttpClientPass());
    }

    public function testThrowsExceptionWhenMissingTokenCollectionService(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The token collection service definition is missing.');

        $this->containerBuilder->compile();
    }

    public function testDoesNothingWhenNoHttpClientTag(): void
    {
        $tokenCollectionDefinition = new Definition(TokenCollection::class);
        $this->containerBuilder->setDefinition(TokenCollection::class, $tokenCollectionDefinition);

        $this->containerBuilder->compile();

        $this->assertFalse($this->containerBuilder->hasDefinition(HttpClientDecorator::class));
    }

    public function testDecoratesHttpClient(): void
    {
        $tokenCollectionDefinition = new Definition(TokenCollection::class);
        $tokenCollectionDefinition->addMethodCall('add', ['process']);
        $this->containerBuilder->setDefinition(TokenCollection::class, $tokenCollectionDefinition);

        $serviceDefinition = new Definition(HttpClientInterface::class);
        $serviceDefinition->setPublic(true); // Avoid service being removed because never used by other services.
        $serviceDefinition->addTag('http_client.client');

        $this->containerBuilder->setDefinition('my_client', $serviceDefinition);

        $this->containerBuilder->compile();

        $this->assertTrue($this->containerBuilder->hasDefinition('my_client'));

        $compiledDefinition = $this->containerBuilder->getDefinition('my_client');
        $this->assertSame(HttpClientDecorator::class, $compiledDefinition->getClass());

        $this->assertTrue($compiledDefinition->hasTag('http_client.client'));

        $this->assertCount(2, $compiledDefinition->getArguments());

        /** @var Definition $tokenCollectionArgument */
        $tokenCollectionArgument = $compiledDefinition->getArgument(1);
        $this->assertCount(1, $tokenCollectionArgument->getMethodCalls());
    }
}
