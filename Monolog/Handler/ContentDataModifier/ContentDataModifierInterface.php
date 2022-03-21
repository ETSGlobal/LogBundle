<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier;

/** Modify message content data.*/
interface ContentDataModifierInterface
{
    public function modify(array &$contentData, array $record): void;
}
