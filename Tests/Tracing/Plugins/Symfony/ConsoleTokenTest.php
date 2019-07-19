<?php
declare(strict_types=1);

namespace Tests\ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\ConsoleToken;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConsoleTokenTest extends TestCase
{
    /** @var TokenCollection */
    private $tokenCollection;

    /** @var ConsoleToken */
    private $consoleToken;

    protected function setUp(): void
    {
        $this->tokenCollection = new TokenCollection();
        $this->consoleToken = new ConsoleToken($this->tokenCollection);
    }

    /**
     * @test
     */
    public function it_creates_global_token(): void
    {
        $this->consoleToken->create();

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

        $this->consoleToken->clear();

        $this->assertNull($this->tokenCollection->getTokenValue('global'));
    }
}
