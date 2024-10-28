<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

trait LogTrait
{
    private const int CONTENT_MAX_SIZE = 100_000;
    private const string SPLIT_SEPARATOR = '...';

    public function isContentExceedMaxSize(string $content): bool
    {
        return mb_strlen($content) > self::CONTENT_MAX_SIZE;
    }

    public function splitContent(string $content): string
    {
        return mb_substr($content, 0, self::CONTENT_MAX_SIZE / 2) .
            self::SPLIT_SEPARATOR .
            mb_substr($content, -self::CONTENT_MAX_SIZE / 2);
    }
}
