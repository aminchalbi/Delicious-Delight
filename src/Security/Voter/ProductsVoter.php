<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Products;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    // Correct constructor method name
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    // Implement the supports method
    protected function supports(string $attribute, $product): bool
    {
        // Check if the attribute is valid and if the product is an instance of Products
        return in_array($attribute, [self::EDIT, self::DELETE]) && $product instanceof Products;
    }

    protected function voteOnAttribute(string $attribute, $product, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Ensure the user is authenticated
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check if the user has admin role
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Handle specific attribute checks
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit();
            case self::DELETE:
                return $this->canDelete();
        }

        return false;
    }

    private function canEdit(): bool
    {
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }

    private function canDelete(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
