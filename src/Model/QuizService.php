<?php

namespace App\Model;

use Doctrine\DBAL\Connection;

class QuizService
{
    public function getQuizzes(Connection $connection) : array
    {
        $quizzes = [];

        $queryBuilder = $connection->createQueryBuilder();

        // todo potreba dodelat bude dahat vsechny tabulky a davat je do $quizzes

        return $quizzes;
    }

}