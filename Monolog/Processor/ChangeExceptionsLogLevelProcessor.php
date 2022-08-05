<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
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
    public function __construct(private array $customExceptionsConfig, private array $httpExceptionsConfig)
    {
    }

    private function determineLogLevel(\Throwable $throwable, int $currentLevel): Level
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $this->determineLogLevelHttpException($throwable, $currentLevel);
        }

        $exceptions = array_keys($this->customExceptionsConfig);

        foreach ($exceptions as $exception) {
            if ($throwable instanceof $exception) {
                return Level::from($this->customExceptionsConfig[$exception]);
            }
        }

        return Level::from($currentLevel);
    }

    private function determineLogLevelHttpException(
        HttpExceptionInterface $httpException,
        int $currentLevel,
    ): Level {
        $exceptions = array_keys($this->httpExceptionsConfig);

        foreach ($exceptions as $exception) {
            if ($httpException instanceof $exception) {
                return Level::from($this->httpExceptionsConfig[$exception]);
            }
        }

        return Level::from($currentLevel);
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        /** @var int $level */
        $level = $record['level'];

        if ($level < 400) {
            return $record;
        }

        if (!is_array($record['context']) || !isset($record['context']['exception'])) {
            return $record;
        }

        $throwable = $record['context']['exception'];

        if (!$throwable instanceof \Throwable) {
            // For some reason the provided value is not an actual exception, so we can't do anything with it
            return $record;
        }

        // Change the log level if necessary
        $modifiedLogLevel = $this->determineLogLevel($throwable, $level);

        if ($level === $modifiedLogLevel->value) {
            return $record;
        }

        return $record->with(level: $modifiedLogLevel);
    }
}
