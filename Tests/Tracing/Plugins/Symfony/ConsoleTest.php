<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\Console;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConsoleTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    /** @var Console */
    private $console;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->console = new Console($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_creates_global_token(): void
    {
        $this->console->create();

        $globalTokenValue = $this->tokenCollection->getTokenValue('global');
        $this->assertNotNull($globalTokenValue);
        $this->assertIsString($globalTokenValue);
    }

    /**
     * @test
     */
    public function it_clears_global_token(): void
    {
        $this->tokenCollection->add('global');

        $this->console->clear();

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }
}
