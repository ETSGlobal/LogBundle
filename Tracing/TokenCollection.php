<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing;

/**
 * TokenCollection holds tokens to be passed to other applications through various
 * communication protocols, like HTTP headers or RabbitMQ message headers.
 *
 * Example usage:
 *
 * $tokenCollection = new TokenCollection();
 *
 * // Add a "request" token on every HTTP request (e.g. at kernel.request event):
 * $tokenCollection->add('request');
 *
 * // Add a "process" token on every long-running process startup (e.g. console application):
 * $tokenCollection->add('process');
 *
 * // Add a "global" token to be transferred to other apps to be able to trace the entire stack for a given
 * // functional event:
 * $tokenCollection->add('global');
 *
 * // If the "global" token already exists in the incoming HTTP headers for example, make it transit unchanged:
 * $tokenCollection->add('global', $request->headers->get('X-Some-Header'));
 */
class TokenCollection implements \IteratorAggregate
{
    /**
     * @var array<string, Token>
     */
    private $tokens = [];

    /**
     * Add a new token to the collection.
     *
     * @param string      $tokenName      the name of the token to be added
     * @param string|null $tokenValue     the value of the token, or null to generate a random value
     * @param bool        $allowOverwrite whether to allow an existing token to be erased
     *
     * @throws \OutOfBoundsException If a token already exists for the given name, and that overwrite is not allowed.
     *
     * @return TokenCollection
     */
    public function add(string $tokenName, ?string $tokenValue = null, bool $allowOverwrite = false): self
    {
        if (\array_key_exists($tokenName, $this->tokens) && !$allowOverwrite) {
            throw new \OutOfBoundsException(sprintf('The token "%s" already exists.', $tokenName));
        }

        $this->tokens[$tokenName] = new Token($tokenName, $tokenValue ?: $this->generateValue($tokenName));

        return $this;
    }

    /**
     * Replace the value of an existing token.
     *
     * @param string      $tokenName  the name of the token to replace
     * @param string|null $tokenValue the new token value, or null to generate a random value
     *
     * @return TokenCollection
     */
    public function replace(string $tokenName, ?string $tokenValue = null): self
    {
        return $this->remove($tokenName)->add($tokenName, $tokenValue);
    }

    /**
     * Remove a token from the collection.
     *
     * @param string $tokenName the name of the token to remove
     * @param bool   $silent    whether an exception should be raised if the token does not exist
     *
     * @throws \OutOfBoundsException If the token does not exist and not in silent mode.
     *
     * @return TokenCollection
     */
    public function remove(string $tokenName, bool $silent = false): self
    {
        if (!$silent && !\array_key_exists($tokenName, $this->tokens)) {
            throw new \OutOfBoundsException(sprintf('The token "%s" doesn\'t exists.', $tokenName));
        }

        unset($this->tokens[$tokenName]);

        return $this;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Get the value of a token.
     *
     * @param string      $tokenName the name of the token to get the value from
     * @param string|null $default   the default value to return if no such token exist
     */
    public function getTokenValue(string $tokenName, ?string $default = null): ?string
    {
        if (!\array_key_exists($tokenName, $this->tokens)) {
            return $default;
        }

        return $this->tokens[$tokenName]->getValue();
    }

    /**
     * Get the values of all tokens.
     *
     * @return array<string>
     */
    public function getTokensValues(): array
    {
        return array_map(
            static function (Token $token): string {
                return $token->getValue();
            },
            $this->tokens
        );
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tokens);
    }

    private function generateValue(string $tokenName): string
    {
        return uniqid($tokenName.'_');
    }
}
