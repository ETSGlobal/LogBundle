<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection\CompilerPass;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpClientDecorator;
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
    private const TOKEN_COLLECTION_SERVICE_ID = 'ets_global_log.tracing.token_collection';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ets_global_log.tracing.token_collection')) {
            throw new \RuntimeException('The token collection service definition is missing.');
        }

        $taggedServices = $container->findTaggedServiceIds('http_client.client');
        foreach ($taggedServices as $id => $attributes) {
            $httpClientDefinition = $container->getDefinition($id);

            $decorator = new Definition(HttpClientDecorator::class);
            $decorator->setDecoratedService($id);
            $decorator->setArgument('$httpClient', $httpClientDefinition);
            $decorator->setArgument('$tokenCollection', new Reference(self::TOKEN_COLLECTION_SERVICE_ID));

            $container->setDefinition(HttpClientDecorator::class, $decorator);
        }
    }
}
