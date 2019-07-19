<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\TokenCollection;

class Console
{
    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(TokenCollection $tokenCollection)
    {
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * Initializes the "global" token with a random value.
     */
    public function create(): void
    {
        $this->tokenCollection->add('global', null, true);
    }

    /**
     * Clears the global token.
     */
    public function clear(): void
    {
        $this->tokenCollection->remove('global');
    }
}
