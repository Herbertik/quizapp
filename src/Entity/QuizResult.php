<?php

namespace App\Entity;

use App\Repository\QuizResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizResultRepository::class)]
class QuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $Percentage = null;

    #[ORM\Column]
    private ?int $userId;
    #[ORM\Column]
    private ?int $quizId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Time = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
    public function setQuizId(int $quizId): static
    {
        $this->quizId = $quizId;

        return $this;
    }

    public function getPercentage(): ?float
    {
        return $this->Percentage;
    }

    public function setPercentage(float $Percentage): static
    {
        $this->Percentage = $Percentage;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->Time;
    }

    public function setTime(\DateTimeInterface $Time): static
    {
        $this->Time = $Time;

        return $this;
    }

}
