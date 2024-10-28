<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LogRequestSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LogTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        memory_reset_peak_usage();

        $request = $event->getRequest();
        $request->attributes->set('__start_time', microtime(true));

        $isContentExceed = $this->isContentExceedMaxSize($request->getContent());

        if (!$isContentExceed) {
            $this->logger->info(
                'Start request',
                [
                    'method' => $request->getMethod(),
                    'request_uri' => $request->getRequestUri(),
                    'headers' => $request->headers->all(),
                    'query' => $request->query->all(),
                    'request' => $request->request->all(),
                    'content' => $request->getContent(),
                    'memory' => memory_get_usage(),
                ],
            );

            return;
        }

        $this->logger->warning(
            'Start request (long request content)',
            [
                'method' => $request->getMethod(),
                'request_uri' => $request->getRequestUri(),
                'headers' => $request->headers->all(),
                'query' => $request->query->all(),
                'request' => $request->request->all(),
                'content' => $this->splitContent($request->getContent()),
                'memory' => memory_get_usage(),
            ],
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }
}
