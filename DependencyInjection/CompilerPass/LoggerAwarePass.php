<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\DependencyInjection\CompilerPass;

use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Automatically inject logger service into tagged services.
 *
 * Finds all tagged services and add logger by setter injection
 * on classes that implements Psr\Log\LoggerAwareInterface.
 */
final class LoggerAwarePass implements CompilerPassInterface
{
    private const TAG_NAME = 'etsglobal_log.logger_aware';

    public function process(ContainerBuilder $container): void
    {
        $taggerServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach (array_keys($taggerServices) as $id) {
            $serviceDefinition = $container->getDefinition($id);

            $reflectionClass = $container->getReflectionClass($serviceDefinition->getClass());
            if (!$reflectionClass instanceof \ReflectionClass) {
                continue;
            }

            if (!$reflectionClass->implementsInterface(LoggerAwareInterface::class)) {
                throw new \LogicException(sprintf(
                    'The service "%s" tagged as "%s" must implement "%s"',
                    $id,
                    self::TAG_NAME,
                    LoggerAwareInterface::class
                ));
            }
            $serviceDefinition->clearTag('monolog.logger');
            $serviceDefinition->addTag('monolog.logger', ['channel' => 'app']);

            $serviceDefinition->addMethodCall('setLogger', [new Reference('logger')]);
        }
    }
}
