<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;

/** @internal */
final class ExtraFieldProcessor implements ProcessorInterface
{
    public function __construct(private string $fieldName, private string $fieldValue)
    {
    }

    public function __invoke(array $record): array
    {
        $record['extra'][$this->fieldName] = $this->fieldValue;

        return $record;
    }
}
