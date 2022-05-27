<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/** @internal */
final class ExtraFieldProcessor implements ProcessorInterface
{
    public function __construct(private string $fieldName, private string $fieldValue)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        /** @var array $extra */
        $extra = $record['extra'];

        $extra[$this->fieldName] = $this->fieldValue;
        $record['extra'] = $extra;

        return $record;
    }
}
