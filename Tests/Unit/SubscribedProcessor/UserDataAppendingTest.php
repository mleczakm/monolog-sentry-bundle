<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\SubscribedProcessor;

use Dziki\MonologSentryBundle\SubscribedProcessor\UserDataAppending;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserDataAppendingTest extends TestCase
{
    /**
     * @test
     */
    public function isSubscribedToKernelRequest()
    {
        $subscribedEvents = UserDataAppending::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertSame(['onKernelRequest', 7], $subscribedEvents[KernelEvents::REQUEST]);
    }

    /**
     * @test
     */
    public function doNothingIfMissingToken(): void
    {
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $userDataAppendingProcessor = new UserDataAppending($tokenStorage);

        $userDataAppendingProcessor->onKernelRequest();

        $record = $userDataAppendingProcessor([]);

        $this->assertSame([], $record);
    }

    /**
     * @test
     */
    public function obtainUsernameIfUserImplementsUserInterface(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('user')
        ;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token)
        ;

        $userDataAppendingProcessor = new UserDataAppending($tokenStorage);
        $userDataAppendingProcessor->onKernelRequest();
    }

    /**
     * @test
     */
    public function appendToLogsUsernameIfUserIsString(): void
    {
        $user = 'user';

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token)
        ;

        $userDataAppendingProcessor = new UserDataAppending($tokenStorage);

        $userDataAppendingProcessor->onKernelRequest();

        $record = $userDataAppendingProcessor([]);

        $this->assertSame(
            ['context' => [
                'user' => [
                    'username' => 'user',
                ],
            ],
            ],
            $record
        );
    }

    /**
     * @test
     */
    public function appendToLogsUsernameIfUserHasToStringMethod(): void
    {
        $user = new class() {
            public function __toString()
            {
                return 'user';
            }
        };

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token)
        ;

        $userDataAppendingProcessor = new UserDataAppending($tokenStorage);
        $userDataAppendingProcessor->onKernelRequest();

        $record = $userDataAppendingProcessor([]);

        $this->assertSame(
            ['context' => [
                'user' => [
                    'username' => 'user',
                ],
            ],
            ],
            $record
        );
    }
}
