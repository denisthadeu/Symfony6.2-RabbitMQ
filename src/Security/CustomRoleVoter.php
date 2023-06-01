<?php
namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CustomRoleVoter extends Voter
{

    protected function supports($attribute, $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $result = Voter::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);

        $result = Voter::ACCESS_DENIED;
        foreach ($roles as $role) {
            if ($attribute === $role)
                return Voter::ACCESS_GRANTED;
        }
        return $result;
    }

    protected function extractRoles(TokenInterface $token)
    {
        return $token->getRoleNames();
    }
}