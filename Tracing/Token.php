<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing;

/**
 * Basic key/value tracing token.
 *
 * Its name can be used to distinguish multiple kinds of tokens, sometimes
 * you may want to generate a certain token in a per-request scope, or maybe
 * in a global, inter-service scope, to be able to trace a functional event
 * through the whole stack of applications.
 *
 * Its value should be a unique, randomly-generated value, to avoid collisions.
 */
class Token
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /**
     * @param string $name  identifier of the token, must be unique in a TokenCollection
     * @param string $value value of the token, must be unique for a given functional event
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
