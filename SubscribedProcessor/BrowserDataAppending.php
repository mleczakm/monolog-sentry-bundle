<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\SubscribedProcessor;

use Dziki\MonologSentryBundle\UserAgent\ParserInterface;
use Dziki\MonologSentryBundle\UserAgent\UserAgent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BrowserDataAppending implements EventSubscriberInterface
{
    /** @var UserAgent */
    private $userAgent;
    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
        ];
    }

    public function __invoke(array $record): array
    {
        if ($this->userAgent) {
            $record['contexts']['browser'] = [
                'name' => $this->userAgent->getBrowseName(),
                'version' => $this->userAgent->getBrowserVersion(),
            ];
            $record['contexts']['os'] = ['name' => $this->userAgent->getPlatform()];
        }

        return $record;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /** @var string $userAgent */
        $userAgent = $event->getRequest()->headers->get('User-Agent', '');

        $this->userAgent = $this->parser->parse($userAgent);
    }
}
