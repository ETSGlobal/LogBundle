<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @internal
 */
final class ETSGlobalLogExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ets_global_log.app_name', $config['app_name']);
        $container->setParameter('ets_global_log.log_format', $config['log_format']);
        $container->setParameter('ets_global_log.handlers.slack.token', $config['slack_handler']['token']);
        $container->setParameter('ets_global_log.handlers.slack.channel', $config['slack_handler']['channel']);
        $container->setParameter('ets_global_log.handlers.slack.icon_emoji', $config['slack_handler']['icon_emoji']);
        $container->setParameter('ets_global_log.handlers.slack.log_level', $config['slack_handler']['log_level']);
        $container->setParameter('ets_global_log.handlers.slack.jira_url', $config['slack_handler']['jira_url']);
        $container->setParameter('ets_global_log.handlers.slack.kibana_url', $config['slack_handler']['kibana_url']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
