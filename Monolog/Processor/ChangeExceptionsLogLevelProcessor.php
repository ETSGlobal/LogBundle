<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Changes the log level on specific exceptions.
 *
 * By default, the log level is CRITICAL, but for some known exceptions such as
 * HttpNotFoundException we want to lower the log level to something like INFO
 * because there is nothing wrong with our application.
 *
 * @internal
 */
final class ChangeExceptionsLogLevelProcessor implements ProcessorInterface
{
    /** @var array<class-string,int> */
    private array $customExceptionsConfig;

    /** @var array<class-string,int> */
    private array $httpExceptionsConfig;

    /**
     * @param array<class-string,int> $customExceptionsConfig
     * @param array<class-string,int> $httpExceptionsConfig
     */
    public function __construct(array $customExceptionsConfig, array $httpExceptionsConfig)
    {
        $this->customExceptionsConfig = $customExceptionsConfig;
        $this->httpExceptionsConfig = $httpExceptionsConfig;
    }

    public function __invoke(array $record): array
    {
        if ($record['level'] < 400) {
            return $record;
        }
        if (!isset($record['context']['exception'])) {
            return $record;
        }

        $throwable = $record['context']['exception'];
        if (!$throwable instanceof \Throwable) {
            // For some reason the provided value is not an actual exception, so we can't do anything with it
            return $record;
        }

        // Change the log level if necessary
        $modifiedLogLevel = $this->determineLogLevel($throwable, $record['level']);

        $record['level'] = $modifiedLogLevel;
        $record['level_name'] = Logger::getLevelName($modifiedLogLevel);

        return $record;
    }

    private function determineLogLevel(\Throwable $throwable, int $currentLevel): int
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $this->determineLogLevelHttpException($throwable, $currentLevel);
        }

        $exceptions = array_keys($this->customExceptionsConfig);

        foreach ($exceptions as $exception) {
            if ($throwable instanceof $exception) {
                return $this->customExceptionsConfig[$exception];
            }
        }

        return $currentLevel;
    }

    private function determineLogLevelHttpException(HttpExceptionInterface $httpException, int $currentLevel): int
    {
        $exceptions = array_keys($this->httpExceptionsConfig);

        foreach ($exceptions as $exception) {
            if ($httpException instanceof $exception) {
                return $this->httpExceptionsConfig[$exception];
            }
        }

        return $currentLevel;
    }
}
