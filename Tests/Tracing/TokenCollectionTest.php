<?php

declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/** @internal */
final class TokenCollectionTest extends TestCase
{
    private TokenCollection $tokenCollection;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
    }

    public function testAddWillThrowInvalidArgumentException(): void
    {
        $this->tokenCollection->add('my_fake_name');

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The token "my_fake_name" already exists.');

        // this token name already added
        $this->tokenCollection->add('my_fake_name');
    }

    public function testAddWithoutValue(): void
    {
        $this->tokenCollection->add('my_fake_name');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertMatchesRegularExpression('/^my_fake_name_[a-z0-9]{13}$/', $tokens['my_fake_name']->getValue());
    }

    public function testAddWithValue(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_fake_value');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_fake_value', $tokens['my_fake_name']->getValue());
    }

    public function testOverwriteWithValue(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_previous_fake_value', true);

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_previous_fake_value', $tokens['my_fake_name']->getValue());

        $this->tokenCollection->add('my_fake_name', 'my_fake_value', true);

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_fake_value', $tokens['my_fake_name']->getValue());
    }

    public function testRemoveDoNothingAndReturnCurrentInstance(): void
    {
        $this->tokenCollection->remove('my_fake_name');
        $this->assertArrayNotHasKey('my_fake_name', $this->tokenCollection->getTokens());
    }

    public function testRemove(): void
    {
        $this->tokenCollection->add('my_fake_name');
        $this->assertArrayHasKey('my_fake_name', $this->tokenCollection->getTokens());

        $this->tokenCollection->remove('my_fake_name');
        $this->assertArrayNotHasKey('my_fake_name', $this->tokenCollection->getTokens());
    }

    public function testReplaceWillAddKeyIfNotExists(): void
    {
        $this->tokenCollection->replace('my_fake_name');
        $this->assertArrayHasKey('my_fake_name', $this->tokenCollection->getTokens());
    }

    public function testReplaceWithoutValue(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_previous_token_value');

        $this->tokenCollection->replace('my_fake_name');
        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertNotEquals('my_previous_token_value', $tokens['my_fake_name']->getValue());
        $this->assertMatchesRegularExpression('/^my_fake_name_[a-z0-9]{13}$/', $tokens['my_fake_name']->getValue());
    }

    public function testReplaceWithValue(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_previous_token_value');

        $this->tokenCollection->replace('my_fake_name', 'my_new_token_value');
        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertNotEquals('my_previous_token_value', $tokens['my_fake_name']->getValue());
        $this->assertEquals('my_new_token_value', $tokens['my_fake_name']->getValue());
    }

    public function testGetTokens(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_token_value', $tokens['my_fake_name']->getValue());
    }

    public function testGetTokensValues(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $tokens = $this->tokenCollection->getTokensValues();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_token_value', $tokens['my_fake_name']);
    }

    public function testGetTokenValueDefault(): void
    {
        $this->assertEquals(
            'my_fake_default_value',
            $this->tokenCollection->getTokenValue('my_fake_name', 'my_fake_default_value'),
        );
    }

    public function testGetTokenValue(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $this->assertEquals('my_token_value', $this->tokenCollection->getTokenValue('my_fake_name'));
    }

    public function testGetIterator(): void
    {
        $this->assertInstanceOf(\Iterator::class, $this->tokenCollection->getIterator());
    }
}
