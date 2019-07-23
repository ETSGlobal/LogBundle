<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\DependencyInjection\CompilerPass;

use ETSGlobal\LogBundle\DependencyInjection\CompilerPass\LoggerAwarePass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
final class LoggerAwarePassTest extends TestCase
{
    /** @test */
    public function it_find_logger_aware_service(): void
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new LoggerAwarePass());

        $loggerDefinition = new Definition(LoggerAwareInterface::class);

        $serviceDefinition = new Definition(LoggerAwareInterface::class);
        $serviceDefinition->setPublic(true);
        $serviceDefinition->addTag('ets_global_log.logger_aware');

        $container->setDefinition('logger', $loggerDefinition);
        $container->setDefinition('definition.id', $serviceDefinition);
        $container->compile();

        $compiledDefinition = $container->getDefinition('definition.id');

        $this->assertTrue($compiledDefinition->hasTag('monolog.logger'));
        $this->assertContains('setLogger', array_column($compiledDefinition->getMethodCalls(), 0));
    }

    /** @test */
    public function it_does_not_find_logger_aware_service(): void
    {
        $this->expectException(\LogicException::class);
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->expectExceptionMessage(
            'The service "definition.id" tagged as "ets_global_log.logger_aware" must implement "Psr\Log\LoggerAwareInterface"'
        );

        $container = new ContainerBuilder();
        $container->addCompilerPass(new LoggerAwarePass());

        $loggerDefinition = new Definition(LoggerAwareInterface::class);
        $serviceDefinition = new Definition(\stdClass::class);
        $serviceDefinition->setPublic(true);
        $serviceDefinition->addTag('ets_global_log.logger_aware');

        $container->setDefinition('logger', $loggerDefinition);
        $container->setDefinition('definition.id', $serviceDefinition);
        $container->compile();

        $compiledDefinition = $container->getDefinition('definition.id');

        $this->assertFalse($compiledDefinition->hasTag('monolog.logger'));
        $this->assertCount(0, $compiledDefinition->getMethodCalls());
    }

    /** @test */
    public function it_is_not_reflectable(): void
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new LoggerAwarePass());

        $loggerDefinition = new Definition(LoggerAwareInterface::class);
        $serviceDefinition = new Definition('notaclass');
        $serviceDefinition->setPublic(true);
        $serviceDefinition->addTag('ets_global_log.logger_aware');

        $container->setDefinition('logger', $loggerDefinition);
        $container->setDefinition('definition.id', $serviceDefinition);
        $container->compile();

        $compiledDefinition = $container->getDefinition('definition.id');

        $this->assertFalse($compiledDefinition->hasTag('monolog.logger'));
        $this->assertCount(0, $compiledDefinition->getMethodCalls());
    }
}
