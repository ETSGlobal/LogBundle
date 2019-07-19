<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\Processor\ProcessorInterface;

/**
 * @internal
 */
final class TokenCollectionProcessor implements ProcessorInterface
{
    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(TokenCollection $tokenCollection)
    {
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $record): array
    {
        foreach ($this->tokenCollection->getTokens() as $token) {
            if (!isset($record['extra'])) {
                $record['extra'] = [];
            }

            $record['extra']['token_'.$token->getName()] = $token->getValue();
        }

        return $record;
    }
}
