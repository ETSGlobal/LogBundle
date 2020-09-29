<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class IgnoreDeprecationHandler extends AbstractHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level, false);
    }

    public function handle(array $record): bool
    {
        if (!isset($record['channel'], $record['message']) || $record['channel'] !== 'php') {
            return false;
        }

        return false !== strpos($record['message'], 'User Deprecated');
    }
}
