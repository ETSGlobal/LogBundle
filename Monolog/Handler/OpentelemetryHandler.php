<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler;

use OpenTelemetry\API\Globals;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;

class OpentelemetryHandler extends Handler
{
    public function __construct()
    {
        parent::__construct(Globals::loggerProvider(), 'info');
    }
}