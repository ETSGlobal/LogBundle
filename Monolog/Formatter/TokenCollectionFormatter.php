<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Formatter;

use ETSGlobal\LogBundle\Tracing\Token;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\Formatter\LineFormatter;

/**
 * @internal
 */
final class TokenCollectionFormatter extends LineFormatter
{
    /**
     * @var TokenCollection
     */
    private $tokenCollection;

    /**
     * @var string Original format
     */
    private $originalFormat;

    public function __construct(
        TokenCollection $tokenCollection,
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false
    ) {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
        $this->originalFormat = $this->format;
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        $this->format = str_replace('%token_collection%', $this->getFormattedPlaceholder(), $this->originalFormat);

        return parent::format($record);
    }

    protected function getFormattedPlaceholder(): string
    {
        $tokens = $this->tokenCollection->getTokens();

        if (0 === \count($tokens)) {
            return '';
        }

        return implode(' ', array_map(static function (Token $token): string {
            return sprintf('%%extra.token_%s%%', $token->getName());
        }, $tokens));
    }
}
