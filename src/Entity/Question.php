<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'question')]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255)]
    public string $question;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id')]
    private ?Quiz $relatedToQuiz;


    public function __construct(
        $question,
        $relatedToQuiz,
    ) {
        $this->question = $question;
        $this->relatedToQuiz = $relatedToQuiz;
    }

    public function getRelatedToQuiz(): ?Quiz
    {
        return $this->relatedToQuiz;
    }
}
