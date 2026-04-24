<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isIsActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été désactivé. Veuillez contacter l\'administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
}
