<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Model\Question\AnswerData;
use App\Model\Question\InputType;
use App\Model\Question\QuestionData;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Controller extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    #[Route('/quiz', name: 'index', methods: [Request::METHOD_GET])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('qaq.*')
            ->from('quiz_app.question', 'qaq');

        $questions = $queryBuilder->fetchAllAssociative();
        foreach ($questions as $question) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $answers = $queryBuilder->select('qaa.id_answer', 'qaa.answer', 'qaqa.is_correct')
                ->from('quiz_app.answer', 'qaa')
                ->join('qaa', 'quiz_app.question_answer', 'qaqa', 'qaqa.id_answer = qaa.id_answer')
                ->where('qaqa.id_question = :id')
                ->setParameter('id', $question['id_question'])
                ->fetchAllAssociative();

            $answersData = [];
            $correctAnswerCount = 0;
            foreach ($answers as $answer) {
                $answersData[] = new AnswerData($answer['id_answer'], $answer['answer'], $answer['is_correct']);
                if ($answer['is_correct'] === true) {
                    $correctAnswerCount++;
                }
            }
            if ($correctAnswerCount > 1) {
                $questionsData[] = new QuestionData($question['question'], $answersData, InputType::Checkbox);
            } else {
                $questionsData[] = new QuestionData($question['question'], $answersData, InputType::Radio);
            }



        }

        return $this->render('/index.html.twig', [
            'controller_name' => 'Controller',
            'questionsData' => $questionsData,
        ]);
    }

    #[Route('/answers', name: 'answers', methods: 'POST')]
    public function indexPost(): Response
    {
        foreach ($questionsData as $questionData) {
            foreach ($_REQUEST as $answerData) {

            }
        }
        return new Response(var_export($_REQUEST));
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            dd($request->request->all());
        }

        return $this->render('login.html.twig', [
            'controller_name' => 'Controller',
            'form' => $form,
        ]);
    }
}