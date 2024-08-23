<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpFoundation;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/** @internal */
final class TracingEventSubscriber implements EventSubscriberInterface
{
    private const int HIGHEST_PRIORITY = 512;
    private const int LOWEST_PRIORITY = -512;

    public function __construct(
        private readonly TokenCollection $tokenCollection,
        private readonly HttpFoundation $httpFoundation,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->httpFoundation->setFromRequest($event->getRequest());
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->httpFoundation->setToResponse($event->getResponse());
    }

    /**
     * Initializes the "global" token with a random value.
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->tokenCollection->add('global', null, true);
    }

    /**
     * Clears the global token.
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->tokenCollection->remove('global');
    }

    /**
     * Clears the global token.
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
        $this->tokenCollection->remove('global');
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
}
