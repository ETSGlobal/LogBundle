<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

trait LogTrait
{
    public function isContentExceedMaxSize(string $content): bool
    {
        return \mb_strlen($content) > 65_536;
    }

    public function splitContent(string $content): string
    {
        return \mb_substr($content, 0, 65_536 / 2) .
            '...' .
            \mb_substr($content, -65_536 / 2);
    }
}
