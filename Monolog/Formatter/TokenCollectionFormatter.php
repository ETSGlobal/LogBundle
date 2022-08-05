<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Formatter;

use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

/** @internal */
final class TokenCollectionFormatter extends LineFormatter
{
    public function __construct(
        private TokenCollection $tokenCollection,
        private ?string $originalFormat = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
    ) {
        parent::__construct($originalFormat, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(LogRecord $record): string
    {
        $this->format = str_replace(
            '%token_collection%',
            $this->getFormattedPlaceholder(),
            $this->originalFormat ?? '',
        );

        return parent::format($record);
    }

    protected function getFormattedPlaceholder(): string
    {
        $tokens = $this->tokenCollection->getTokens();

        if (0 === \count($tokens)) {
            return '';
        }

        return implode(
            ' ',
            array_map(
                static fn (Token $token): string => sprintf('%%extra.token_%s%%', $token->getName()),
                $tokens,
            ),
        );
    }
}
