<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TokenCollectionTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
    }

    /**
     * @test
     */
    public function add_will_throw_invalid_argument_exception(): void
    {
        $this->tokenCollection->add('my_fake_name');

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The token "my_fake_name" already exists.');

        // this token name already added
        $this->tokenCollection->add('my_fake_name');
    }

    /**
     * @test
     */
    public function add_without_value(): void
    {
        $this->tokenCollection->add('my_fake_name');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertRegExp('/^my_fake_name_[a-z0-9]{13}$/', $tokens['my_fake_name']->getValue());
    }

    /**
     * @test
     */
    public function add_with_value(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_fake_value');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_fake_value', $tokens['my_fake_name']->getValue());
    }

    /**
     * @test
     */
    public function overwrite_with_value(): void
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

    /**
     * @test
     */
    public function remove_will_throw_invalid_argument_exception(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The token "my_fake_name" doesn\'t exists.');

        $this->tokenCollection->remove('my_fake_name');
    }

    /**
     * @test
     */
    public function remove_silently(): void
    {
        $this->tokenCollection->remove('my_fake_name', true);
        $this->assertArrayNotHasKey('my_fake_name', $this->tokenCollection->getTokens());
    }

    /**
     * @test
     */
    public function remove(): void
    {
        $this->tokenCollection->add('my_fake_name');
        $this->assertArrayHasKey('my_fake_name', $this->tokenCollection->getTokens());

        $this->tokenCollection->remove('my_fake_name');
        $this->assertArrayNotHasKey('my_fake_name', $this->tokenCollection->getTokens());
    }

    /**
     * @test
     */
    public function replace_will_throw_out_of_bounds_exception(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The token "my_fake_name" doesn\'t exists.');

        $this->tokenCollection->replace('my_fake_name');
    }

    /**
     * @test
     */
    public function replace_without_value(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_previous_token_value');

        $this->tokenCollection->replace('my_fake_name');
        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertNotEquals('my_previous_token_value', $tokens['my_fake_name']->getValue());
        $this->assertRegExp('/^my_fake_name_[a-z0-9]{13}$/', $tokens['my_fake_name']->getValue());
    }

    /**
     * @test
     */
    public function replace_with_value(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_previous_token_value');

        $this->tokenCollection->replace('my_fake_name', 'my_new_token_value');
        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertNotEquals('my_previous_token_value', $tokens['my_fake_name']->getValue());
        $this->assertEquals('my_new_token_value', $tokens['my_fake_name']->getValue());
    }

    /**
     * @test
     */
    public function get_tokens(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $tokens = $this->tokenCollection->getTokens();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_fake_name', $tokens['my_fake_name']->getName());
        $this->assertEquals('my_token_value', $tokens['my_fake_name']->getValue());
    }

    /**
     * @test
     */
    public function get_tokens_values(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $tokens = $this->tokenCollection->getTokensValues();
        $this->assertArrayHasKey('my_fake_name', $tokens);
        $this->assertEquals('my_token_value', $tokens['my_fake_name']);
    }

    /**
     * @test
     */
    public function get_token_value_default(): void
    {
        $this->assertEquals(
            'my_fake_default_value',
            $this->tokenCollection->getTokenValue('my_fake_name', 'my_fake_default_value')
        );
    }

    /**
     * @test
     */
    public function get_token_value(): void
    {
        $this->tokenCollection->add('my_fake_name', 'my_token_value');

        $this->assertEquals('my_token_value', $this->tokenCollection->getTokenValue('my_fake_name'));
    }

    /**
     * @test
     */
    public function get_iterator(): void
    {
        $this->assertInstanceOf(\Iterator::class, $this->tokenCollection->getIterator());
    }
}
