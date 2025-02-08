<?php

namespace App\Model\User;

use App\Entity\User;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public static function createUserFromForm(UserPasswordHasherInterface $passwordHasher, $form) : User
    {
        $user = new User();
        $user->setEmail($form->get('email')->getData());
        $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
        $user->setPassword($hashedPassword);
        $user->setNick($form->get('nick')->getData());
        return $user;
    }
}