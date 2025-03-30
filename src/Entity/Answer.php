<?php

namespace App\Entity;
use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answer')]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $text;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $isTrue;
    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    private int $relatedToQuestion;


    public function __construct(
        $text,
        $isTrue,
        $relatedToQuestion,
    ) {
        $this->text = $text;
        $this->isTrue = $isTrue;
        $this->relatedToQuestion = $relatedToQuestion;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

}