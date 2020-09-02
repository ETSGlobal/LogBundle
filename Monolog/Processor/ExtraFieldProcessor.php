<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * @internal
 */
final class ExtraFieldProcessor implements ProcessorInterface
{
    /** @var string */
    private $fieldName;

    /** @var string */
    private $fieldValue;

    public function __construct(string $fieldName, string $fieldValue)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $record): array
    {
        if (!isset($record['extra'])) {
            $record['extra'] = [];
        }

        $record['extra'][$this->fieldName] = $this->fieldValue;

        return $record;
    }
}
