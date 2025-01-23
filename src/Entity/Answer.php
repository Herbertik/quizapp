<?php

namespace App\Entity;
use App\Repository\AnswerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answer_')]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        public string $answer,
    )
    {
    }

}