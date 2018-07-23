<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\SubscribedProcessor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserDataAppending implements EventSubscriberInterface
{
    /** @var UserInterface */
    private $user;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function __invoke(array $record)
    {
        $record['context']['user'] = [
            'username' => $this->user ? $this->user->getUsername() : 'Anonymous',
        ];

        return $record;
    }

    public function onKernelRequest(): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        $this->user = $user;
    }
}
