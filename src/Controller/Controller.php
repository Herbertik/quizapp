<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizResult;
use App\Form\QuizzesType;
use App\Model\Question\AnswerData;
use App\Model\Question\InputType;
use App\Model\Question\QuestionData;
use App\Repository\QuizRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class Controller extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly QuizRepository $quizRepository,
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/', name: 'index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(): Response
    {
        if ($this->security->getUser() !== null) {
            return $this->redirectToRoute("mainMenu");
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/mainMenu/{limit}',name: 'mainMenu', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function mainMenu(Request $request, int $limit = 10, int $loadAtTheTime = 10): Response
    {
        $form = $this->createForm(QuizzesType::class);
        $form->handleRequest($request);

        $quizzes = $this->quizRepository->findByExampleField(1, $limit);

        $form->add('limit', HiddenType::class, ['data' => $limit + $loadAtTheTime]);

        return $this->render('index.html.twig',
        [
            'form' => $form->createView(),
            'limit' => $limit + $loadAtTheTime,
            'quizzes' => $quizzes,
        ]);
    }

    #[Route(path: '/quiz/{quizName}',name: 'quiz', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function quiz(string $quizName,Request $request): Response
    {

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('q.*')
            ->from('public.quiz', 'q')
            ->where('q.name = :quizName')
            ->setParameter('quizName', $quizName);

        $quiz = $queryBuilder->fetchAllAssociative();

        $queryBuilder = $this->connection->createQueryBuilder();

        $questions =  $queryBuilder->select('qaq.*')
            ->from('public.question', 'qaq')
            ->where('qaq.quiz_id = :id')
            ->setParameter('id', $quiz[0]['id'])
            ->fetchAllAssociative();

        $correctAnswers = [];
        $count = 0;
        foreach ($questions as $question) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $answers = $queryBuilder->select('qaa.*')
                ->from('public.answer', 'qaa')
                ->where('qaa.question_id = :id')
                ->setParameter('id', $question['id'])
                ->fetchAllAssociative();


            $answersData = [];
            $correctAnswerCount = 0;
            $correctAnswers[] = [];
            $count = 0;
            foreach ($answers as $answer) {

                $answersData[] = new AnswerData($answer['id'], $answer['text'], $answer['is_true']);
                if ($answer['is_true'] === true) {
                    $correctAnswerCount++;
                    $correctAnswers[$count][] = $answer['text'];
                }

            }
            $count++;
            if ($correctAnswerCount > 1) {
                $questionsData[] = new QuestionData($question['question'], $answersData, InputType::Checkbox);
            } else {
                $questionsData[] = new QuestionData($question['question'], $answersData, InputType::Radio);
            }
        }
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->addFlash('notice','vase odpovedi budou zapsany');
            $correctlyAnswered= 0;
            $wrongAnswered = 0;
            $answered = [];
            $correctAnswersCount = 0;
            $formData = $_POST;
            array_pop($formData);
            foreach ($formData as $data) {
                $answered[]  = $formData[$data];
            }
            foreach ($correctAnswers as $answer) {
                $correctAnswersCount += count($answer);
                $correctlyAnswered += count(array_intersect($answer, $answered));
                $wrongAnswered = count(array_diff($answered, $answer));
            }
            $score = ($correctlyAnswered / $wrongAnswered) * 100;

            $quizResult = new QuizResult();

            $quizResult->setPercentage($score);
            $quizResult->setTime(new DateTime('@'.strtotime('now')));
            $quizResult->setUserId($this->security->getUser()->getId());
            $quizResult->setQuizId($quiz[0]['id']);

            $this->entityManager->clear();
            $this->entityManager->persist($quizResult);
            $this->entityManager->flush();

            return $this->redirectToRoute("mainMenu");
        }

        return $this->render('quiz.html.twig',
        [
            'form' => $form->createView(),
            'quizName' => $quizName,
            'questionsData' => $questionsData,
        ]);
    }

    #[Route('/profile', name: 'profile', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function profile(): Response
    {
        $queryBuilder = $this->connection->createQueryBuilder();    // count all created
        $quizzesCreated = $queryBuilder->select('count(pqz.*)')
            ->from('public.quiz', 'pqz')
            ->where('pqz.creator = :creator')
            ->setParameter('creator', $this->security->getUser()->getNick())
            ->fetchAllAssociative();


        $queryBuilder = $this->connection->createQueryBuilder();    // count all answered by user
        $quizzesFinished = $queryBuilder->select('count(pqr.*)')
            ->from('public.quiz_result', 'pqr')
            ->where('pqr.user_id = :user')
            ->setParameter('user', $this->security->getUser()->getId())
            ->fetchAllAssociative();


        $queryBuilder = $this->connection->createQueryBuilder();    // Select last quizzes with their name and order them
        $lastQuizzes = $queryBuilder->select('pqr.*, pqz.name AS quiz_name, pqz.creator AS quiz_creator')
        ->from('public.quiz_result', 'pqr')
            ->innerJoin('pqr', 'public.quiz', 'pqz', 'pqr.quiz_id = pqz.id')
            ->where('pqr.user_id = :user')
            ->setParameter('user', $this->security->getUser()->getId())
            ->setMaxResults(10)
            ->orderBy('pqr.time', 'DESC')
            ->fetchAllAssociative();

        $userName = $this->security->getUser()->getNick();

        return $this->render('profile.html.twig',[
            'quizzesCreated' => $quizzesCreated,
            'quizzesFinished' => $quizzesFinished,
            'lastQuizzes' => $lastQuizzes,
            'userName' => $userName,
        ]);


    }

    #[Route('/logout', name: 'app_logout', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function logout(): Response
    {
        throw new \LogicException('loginException');
    }

    #[Route('/yourQuizzes', name: 'yourQuizzes', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function yourQuizzes(): Response
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $quizzes = $queryBuilder
            ->select('q.id, q.name, COUNT(qr.id) AS quizResultsCount')
            ->from('public.quiz', 'q')
            ->leftJoin('q', 'public.quiz_result', 'qr', 'q.id = qr.quiz_id')
            ->where('q.creator = :user')
            ->setParameter('user', $this->security->getUser()->getNick())
            ->groupBy('q.id, q.name')
            ->fetchAllAssociative();

        return $this->render('yourQuizzes.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }
    #[Route('/quizResults/{quizId}', name: 'quizResults')]
    public function quizResults(int $quizId): Response
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $results = $queryBuilder->select('pqr.*, u.nick')
            ->from('public.quiz_result', 'pqr')
            ->innerJoin('pqr', 'public.user', 'u', 'pqr.user_id = u.id')
            ->where('pqr.quiz_id = :quiz_id')
            ->setParameter('quiz_id', $quizId)
            ->fetchAllAssociative();

        return $this->render('quiz_results.html.twig', [
            'results' => $results
        ]);
    }

    #[Route('create_quiz', name: 'create_quiz')]
    public function createQuiz(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $quiz = new Quiz();
            $quiz->setName($request->request->get('quiz_name'));
            $quiz->setCreator($this->security->getUser()->getNick());
            $quiz->setCompleted(0);

            $this->entityManager->persist($quiz);
            $this->entityManager->flush();

            return $this->redirectToRoute('add_question', ['quizId' => $quiz->getId()]);
        }

        return $this->render('MakingQuiz/create_quiz.html.twig');
    }

    #[Route('add-question/{quizId}', name: 'add_question')]
    public function addQuestion(int $quizId, Request $request): Response
    {
        $quiz = $this->entityManager->getRepository(Quiz::class)->find($quizId);


        $form = $this->createForm(FormType::class)
            ->add('question', TextType::class, [
                'label' => 'Znění otázky   ',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Přidat otázku',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $questionText = $data['question'];

            $question = new Question($questionText, $quiz);
            $this->entityManager->persist($question);
            $this->entityManager->flush();

            return $this->redirectToRoute('add_answers', [
                'questionId' => $question->getId(),
                'quizId' => $quizId,
                'answers' => 1,
            ]);
        }

        return $this->render('MakingQuiz/add_question.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add-answers/{quizId}/{questionId}/{answers}', name: 'add_answers')]
    public function addAnswers(int $quizId, int $questionId, Request $request, int $answers): Response
    {
        $question = $this->entityManager->getRepository(Question::class)->find($questionId);
        $form = $this->createForm(FormType::class);

        for ($i = 0; $i < $answers; $i++) {
            $form->add('answer' . $i, TextType::class, [
                'label' => 'Odpověď č. ' . ($i + 1),
                'required' => true,
            ])
                ->add('isCorrect' . $i, CheckboxType::class, [
                    'label' => 'Je odpověď č.' . ($i + 1). ' právná ',
                    'required' => false,
                ]);
        }

        $form->add('save', SubmitType::class, ['label' => 'Uložit odpovědi']);
        $form->add('limit', HiddenType::class, ['data' => $answers++]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            for ($i = 0; $i < $answers; $i++) {
                $textKey = 'answer' . $i;
                $correctKey = 'isCorrect' . $i;

                if (!isset($data[$textKey]) || trim($data[$textKey]) === '') {
                    continue;
                }

                $text = $data[$textKey];
                $isCorrect = $data[$correctKey] ?? false;

                $answer = new Answer($text, $isCorrect, $question);
                $this->entityManager->persist($answer);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('add_question', ['quizId' => $quizId]);
        }

        return $this->render('MakingQuiz/add_answer.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
            'quizId' => $quizId,
            'answers' => $answers++,
        ]);
    }
}
