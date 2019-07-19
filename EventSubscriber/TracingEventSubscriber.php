<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\Console;
use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpKernel;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
final class TracingEventSubscriber implements EventSubscriberInterface
{
    private const HIGHEST_PRIORITY = 255;
    private const LOWEST_PRIORITY = -255;

    /** @var Console */
    private $console;

    /** @var HttpKernel */
    private $httpKernel;

    public function __construct(Console $console, HttpKernel $httpKernel)
    {
        $this->console = $console;
        $this->httpKernel = $httpKernel;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', self::HIGHEST_PRIORITY],
            ],
            KernelEvents::RESPONSE => [
                ['onKernelResponse', self::LOWEST_PRIORITY],
            ],
            KernelEvents::TERMINATE => [
                ['onKernelTerminate', self::LOWEST_PRIORITY],
            ],
            ConsoleEvents::COMMAND => [
                ['onConsoleCommand', self::HIGHEST_PRIORITY],
            ],
            ConsoleEvents::TERMINATE => [
                ['onConsoleTerminate', self::LOWEST_PRIORITY],
            ],
        ];
    }

    /**
     * @param GetResponseEvent|RequestEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $this->httpKernel->setFromRequest($event->getRequest());
    }

    /**
     * @param FilterResponseEvent|ResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $this->httpKernel->setToResponse($event->getResponse());
    }

    /**
     * @param PostResponseEvent|TerminateEvent $event
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $this->httpKernel->clear();
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->console->create();
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->console->clear();
    }
}