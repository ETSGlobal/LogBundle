<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    // phpcs:ignore Generic.Files.LineLength.TooLong
    private const DEFAULT_LOG_FORMAT = "[%%datetime%%][%%token_collection%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n";
    private const DEFAULT_SLACK_CHANNEL = '#random';
    private const DEFAULT_SLACK_ICON_EMOJI = ':warning';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('ets_global_log');
        $rootNode
            ->children()
                ->scalarNode('app_name')->cannotBeEmpty()->isRequired()->end()
                ->scalarNode('log_format')->cannotBeEmpty()->defaultValue(self::DEFAULT_LOG_FORMAT)->end()
                ->arrayNode('slack_handler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('token')->cannotBeEmpty()->defaultValue('')->end()
                        ->scalarNode('channel')->cannotBeEmpty()->defaultValue(self::DEFAULT_SLACK_CHANNEL)->end()
                        ->scalarNode('icon_emoji')->cannotBeEmpty()->defaultValue(self::DEFAULT_SLACK_ICON_EMOJI)->end()
                        ->scalarNode('log_level')->cannotBeEmpty()->defaultValue(Logger::ERROR)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
