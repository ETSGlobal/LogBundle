<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;

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

    public static function logDisabled(Request $request): bool
    {
        return \in_array($request->getRequestUri(), ['/monitor/health/', '/monitor/health/http_status_checks'], true);
    }
}
