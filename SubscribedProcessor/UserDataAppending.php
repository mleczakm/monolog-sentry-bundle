<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\SubscribedProcessor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserDataAppending implements EventSubscriberInterface
{
    /** @var string */
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
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    public function __invoke(array $record)
    {
        if ($this->user) {
            $record['context']['user'] = [
                'username' => $this->user,
            ];
        }

        return $record;
    }

    public function onKernelRequest(): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return;
        }

        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            $this->user = $user->getUsername();

            return;
        }

        $this->user = (string) $user;
    }
}
