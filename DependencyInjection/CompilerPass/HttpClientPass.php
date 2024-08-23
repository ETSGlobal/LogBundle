<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection\CompilerPass;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will create decorated instances of HttpClient.
 *
 * All services with tag `http_client.client` will be decorated by a
 * HttpClientDecorator so we make sure all scoped clients are taken into account.
 * All these original HttpClient services will be substituted by the new decorated services.
 */
class HttpClientPass implements CompilerPassInterface
{
    private const int DECORATOR_PRIORITY = 5;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(TokenCollection::class)) {
            throw new \RuntimeException('The token collection service definition is missing.');
        }

        $taggedServices = $container->findTaggedServiceIds('http_client.client');
        foreach (array_keys($taggedServices) as $id) {
            $httpClientDefinition = $container->getDefinition($id);

            $decorator = new Definition(HttpClientDecorator::class);
            $decorator->setDecoratedService($id, null, self::DECORATOR_PRIORITY);
            $decorator->setArgument('$httpClient', $httpClientDefinition);
            $decorator->setArgument('$tokenCollection', new Reference(TokenCollection::class));

            $container->setDefinition(HttpClientDecorator::class, $decorator);
        }
    }
}
