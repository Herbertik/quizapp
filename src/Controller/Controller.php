<?php

namespace App\Controller;

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
use Doctrine\DBAL\Connection;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Model\User\UserService;
use Symfony\Component\Security\Core\User\UserInterface;


class Controller extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UserService $userService,
        private readonly Security $security,
        private readonly UserRepository $repository,
        private readonly QuizRepository $quizRepository,
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


        $form = $this->createForm(QuizzesType::class);
        $quizzes = $this->quizRepository->findByExampleField(1, $limit);
        foreach ($quizzes as $quiz) {
            $form->add($quiz->getName(), ButtonType::class);
        }
        $form->add('limit', HiddenType::class, ['data' => $limit + $loadAtTheTime]);

        $quizzesName = [];
        foreach ($quizzes as $quiz) {
            $quizzesName[] = $quiz->getName();
        }

        return $this->render('index.html.twig',
        [
            'form' => $form->createView(),
            'limit' => $limit + $loadAtTheTime,
            'quizzes' => $quizzes,
            'quizzesName' => $quizzesName,
        ]);
    }
}


