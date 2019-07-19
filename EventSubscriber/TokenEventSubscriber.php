<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

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
final class TokenEventSubscriber implements EventSubscriberInterface
{
    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(TokenCollection $tokenCollection)
    {
        $this->tokenCollection = $tokenCollection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            [KernelEvents::REQUEST => ['createGlobalTokenFromHeaders', 255]],
            [ConsoleEvents::COMMAND => ['initializeGlobalToken', 255]],
            [KernelEvents::RESPONSE => ['setTokensInResponseHeaders', -255]],
            [KernelEvents::TERMINATE => ['clearGlobalToken', -255]],
            [ConsoleEvents::TERMINATE => ['clearGlobalToken', -255]],
        ];
    }

    /**
     * Adds the "global" token to the TokenCollection.
     *
     * If the "global" token is not found in the incoming request HTTP headers,
     * it will be initialized, otherwise its value is preserved.
     *
     * @param GetResponseEvent|RequestEvent $event
     */
    public function createGlobalToken(GetResponseEvent $event): void
    {
        $header = $event->getRequest()->headers->get('X-Token-Global');
        if (\is_array($header)) {
            $header = implode('', $header);
        }

        $this->tokenCollection->add(
            'global',
            $header ?? null,
            true
        );
    }

    /**
     * Initializes the "global" token with a random value.
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function initializeGlobalToken(ConsoleCommandEvent $event): void
    {
        $this->tokenCollection->add('global', null, true);
    }

    /**
     * Sets all tokens in the response headers.
     *
     * @param FilterResponseEvent|ResponseEvent $event
     */
    public function setTokensInResponseHeaders(FilterResponseEvent $event): void
    {
        $response = $event->getResponse();

        foreach ($this->tokenCollection->getTokens() as $token) {
            $response->headers->set(sprintf('X-Token-%s', ucfirst($token->getName())), $token->getValue());
        }
    }

    /**
     * Clears the global token.
     *
     * @param ConsoleTerminateEvent|PostResponseEvent|TerminateEvent $event
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function clearGlobalToken($event): void
    {
        $this->tokenCollection->remove('global');
    }
}
