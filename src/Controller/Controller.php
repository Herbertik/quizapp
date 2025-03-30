<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizResult;
use App\Entity\User;
use App\Form\FrontType;
use App\Form\LoginType;
use App\Form\QuizzesType;
use App\Form\RegisterType;
use App\Model\Question\AnswerData;
use App\Model\Question\InputType;
use App\Model\Question\Login\LoginData;
use App\Model\Question\QuestionData;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function index(Request $request,UserPasswordHasherInterface $passwordHasher): Response
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

            // todo same popup massage
            $quizResult = new QuizResult();
            $quizResult->setPercentage($score);

            $date = new DateTime('@'.strtotime('now'));
            $quizResult->setTime($date);
            $userId = $this->security->getUser()->getId();
            $quizResult->setUserId($userId);
            $quizResult->setQuizId($quiz[0]['id']);

            $this->entityManager->clear();
            $this->entityManager->persist($quizResult);
            $this->entityManager->flush(); // todo idk co to je za chybu

            return $this->redirectToRoute("mainMenu");
        }

        return $this->render('quiz.html.twig',
        [
            'form' => $form->createView(),
            'quizName' => $quizName,
            'questionsData' => $questionsData,
        ]);
    }

    #[Route('/answers', name: 'answers', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function indexPost(): Response
    {
        return $this->render('answers.html.twig');
    }

    #[Route('/newQuiz', name: 'newQuiz', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function newQuiz(Request $request,): Response {

        $form = $this->createForm(FormType::class);
        $form->add('name', TextType::class,[]);
        $form->add('submit', SubmitType::class,[]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $quiz = new Quiz();
            $quiz->setName($form->get('name')->getData())->setCreator($this->security->getUser()->getId());
            return $this->redirectToRoute('creatingQuiz');
        }

        return $this->render('newQuiz.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }
    #[Route('/CreatingQuiz/', name: 'creatingQuiz', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function creatingQuiz(Request $request,int $questionsCount = 0): Response                            //todo potreba udelat tvoreni quizu
    {
        $form = $this->createForm(FormType::class);
        $form->add('questionsCount', HiddenType::class,['data'=>$questionsCount++]);
        for ($i = 0; $i < $questionsCount; $i++) {
            $form->add('question'.$i, TextType::class,[]);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

        }


        return $this->render('creatingQuiz.html.twig',[

        ]);
    }
    #[Route('/profile', name: 'profile', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function profile(): Response
    {
        $queryBuilder = $this->connection->createQueryBuilder();    // count all created
        $quizzesCreated = $queryBuilder->select('count(pqz.*)')
            ->from('public.quiz', 'pqz')
            ->where('pqz.creator = :creator')
            ->setParameter('creator', $this->security->getUser()->getId())
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

        return $this->render('profile.html.twig',[
            'quizzesCreated' => $quizzesCreated,
            'quizzesFinished' => $quizzesFinished,
            'lastQuizzes' => $lastQuizzes,
        ]);
    }
    #[Route('/logout', name: 'app_logout', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function logout(): Response
    {
        throw new \LogicException('loginException');
    }
}
