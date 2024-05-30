<?php

namespace App\Model\Question;

readonly class QuestionData
{
    public function __construct(
        public string $question,

        /**
         * @var array<int, AnswerData> $answers
         */
        public array $answers,
    ) {
    }
}
