<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\EventSubscriber;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LogTerminateSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LogTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $request = $event->getRequest();
        $startTime = $request->attributes->get('__start_time', 0);

        $content = $event->getResponse()->getContent() !== false ? $event->getResponse()->getContent() : '';

        $isContentExceed = $this->isContentExceedMaxSize($content);

        if (!$isContentExceed) {
            $this->logger->info(
                'Finish request',
                [
                    'method' => $request->getMethod(),
                    'request_uri' => $request->getRequestUri(),
                    'headers' => $request->headers->all(),
                    'query' => $request->query->all(),
                    'response' => $content,
                    'time' => microtime(true) - $startTime,
                    'memory_peak' => memory_get_peak_usage(),
                    'memory' => memory_get_usage(),
                ],
            );

            return;
        }

        $this->logger->info(
            'Finish request (long response content)',
            [
                'method' => $request->getMethod(),
                'request_uri' => $request->getRequestUri(),
                'headers' => $request->headers->all(),
                'query' => $request->query->all(),
                'response' => $this->splitContent($content),
                'time' => microtime(true) - $startTime,
                'memory_peak' => memory_get_peak_usage(),
                'memory' => memory_get_usage(),
            ],
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate'],
        ];
    }
}
