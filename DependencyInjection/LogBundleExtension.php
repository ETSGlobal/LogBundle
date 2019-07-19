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
final class LogBundleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('etsglobal_log.app_name', $config['app_name']);
        $container->setParameter('etsglobal_log.log_format', $config['log_format']);
        $container->setParameter('etsglobal_log.handlers.slack.enabled', $config['slack_handler']['enabled']);
        $container->setParameter('etsglobal_log.handlers.slack.tokenGlobalProvider', $config['slack_handler']['tokenGlobalProvider']);
        $container->setParameter('etsglobal_log.handlers.slack.channel', $config['slack_handler']['channel']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
