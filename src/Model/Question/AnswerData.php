<?php

namespace App\Model\Question;

readonly class AnswerData
{
    public function __construct(
        public int $idAnswer,
        public mixed $answer,
        public bool $isCorrect,
    ) {
    }
}
