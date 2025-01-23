<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(type: 'string', length: 63)]
        public string $nick,
        #[ORM\Column(type: 'string', length: 127)]
        public string $email,
        #[ORM\Column(type: 'string', length: 63)]
        public string $password,
    )
    {
    }

}
