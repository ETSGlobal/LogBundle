<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

trait LogTrait
{
    public function isContentExceedMaxSize(string $content): bool
    {
        return mb_strlen($content) > 100_000;
    }

    public function splitContent(string $content): string
    {
        return mb_substr($content, 0, 100_000 / 2) .
            '...' .
            mb_substr($content, -100_000 / 2);
    }
}
