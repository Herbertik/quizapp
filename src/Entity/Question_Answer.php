<?php

namespace App\Entity;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'question_answer')]
class Question_Answer
{
    public function __construct(
        #[ORM\Column(type: 'integer')]
        public int $idQuestion,
        #[ORM\Column(type: 'integer')]
        public int $idAnswer,
        #[ORM\Column(type: 'boolean')]
        bool $isCorrect,
    )
    {
    }

}