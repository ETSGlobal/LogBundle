<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection;

use Monolog\Level;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/** @internal */
final class Configuration implements ConfigurationInterface
{
    private const string DEFAULT_APP_NAME = 'default';
    // phpcs:ignore Generic.Files.LineLength.TooLong
    private const string DEFAULT_LOG_FORMAT = "[%%datetime%%][%%token_collection%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n";
    private const string DEFAULT_SLACK_CHANNEL = '#random';
    private const string DEFAULT_SLACK_ICON_EMOJI = ':warning';
    private const array DEFAULT_HTTP_EXCEPTIONS_LEVELS = [
        BadRequestHttpException::class => Level::Warning->value,
        AccessDeniedHttpException::class => Level::Warning->value,
        NotFoundHttpException::class => Level::Warning->value,
        UnauthorizedHttpException::class => Level::Warning->value,
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ets_global_log');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('app_name')->cannotBeEmpty()->defaultValue(self::DEFAULT_APP_NAME)->end()
                ->scalarNode('log_format')->cannotBeEmpty()->defaultValue(self::DEFAULT_LOG_FORMAT)->end()
                ->arrayNode('http_exceptions_levels')
                    ->defaultValue(self::DEFAULT_HTTP_EXCEPTIONS_LEVELS)
                    ->useAttributeAsKey('name')
                    ->prototype('enum')->values([
                        Level::Debug->value,
                        Level::Info->value,
                        Level::Notice->value,
                        Level::Warning->value,
                        Level::Error->value,
                        Level::Critical->value,
                        Level::Alert->value,
                        Level::Emergency->value,
                    ])->end()
                ->end()
                ->arrayNode('custom_exceptions_levels')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('slack_handler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('token')->cannotBeEmpty()->defaultValue('')->end()
                        ->scalarNode('channel')->cannotBeEmpty()->defaultValue(self::DEFAULT_SLACK_CHANNEL)->end()
                        ->scalarNode('icon_emoji')->cannotBeEmpty()->defaultValue(self::DEFAULT_SLACK_ICON_EMOJI)->end()
                        ->scalarNode('log_level')->cannotBeEmpty()->defaultValue(Level::Error->value)->end()
                        ->scalarNode('jira_url')->cannotBeEmpty()->defaultValue('')->end()
                        ->scalarNode('kibana_url')->cannotBeEmpty()->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
