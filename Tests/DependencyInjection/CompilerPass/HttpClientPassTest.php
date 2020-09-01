<?php

declare(strict_types=1);

namespace DependencyInjection\CompilerPass;

use ETSGlobal\LogBundle\DependencyInjection\CompilerPass\HttpClientPass;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientPassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $containerBuilder;

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
        $this->containerBuilder->setDefinition('ets_global_log.tracing.token_collection', $tokenCollectionDefinition);

        $this->containerBuilder->compile();

        Assert::assertFalse($this->containerBuilder->hasDefinition(HttpClientDecorator::class));
    }

    public function testDecoratesHttpClient(): void
    {
        $tokenCollectionDefinition = new Definition(TokenCollection::class);
        $tokenCollectionDefinition->addMethodCall('add', ['process']);
        $this->containerBuilder->setDefinition('ets_global_log.tracing.token_collection', $tokenCollectionDefinition);

        $serviceDefinition = new Definition(HttpClientInterface::class);
        $serviceDefinition->setPublic(true); // Avoid service being removed because never used by other services.
        $serviceDefinition->addTag('http_client.client');

        $this->containerBuilder->setDefinition('my_client', $serviceDefinition);

        $this->containerBuilder->compile();

        Assert::assertTrue($this->containerBuilder->hasDefinition('my_client'));

        $compiledDefinition = $this->containerBuilder->getDefinition('my_client');
        Assert::assertSame(HttpClientDecorator::class, $compiledDefinition->getClass());

        Assert::assertTrue($compiledDefinition->hasTag('http_client.client'));

        Assert::assertCount(2, $compiledDefinition->getArguments());

        /** @var Definition $tokenCollectionArgument */
        $tokenCollectionArgument = $compiledDefinition->getArgument(1);
        Assert::assertCount(1, $tokenCollectionArgument->getMethodCalls());
    }
}
