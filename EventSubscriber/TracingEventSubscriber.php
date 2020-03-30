<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use ETSGlobal\LogBundle\Tracing\Plugins\Symfony\HttpFoundation;
use ETSGlobal\LogBundle\Tracing\TokenCollection;
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
    private const HIGHEST_PRIORITY = 512;
    private const LOWEST_PRIORITY = -512;

    /** @var TokenCollection */
    private $tokenCollection;

    /** @var HttpFoundation */
    private $httpFoundation;

    public function __construct(TokenCollection $tokenCollection, HttpFoundation $httpFoundation)
    {
        $this->tokenCollection = $tokenCollection;
        $this->httpFoundation = $httpFoundation;
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
    public function onKernelRequest($event): void
    {
        $this->httpFoundation->setFromRequest($event->getRequest());
    }

    /**
     * @param FilterResponseEvent|ResponseEvent $event
     */
    public function onKernelResponse($event): void
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
     * @param PostResponseEvent|TerminateEvent $event
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function onKernelTerminate($event): void
    {
        $this->tokenCollection->remove('global');
    }
}
