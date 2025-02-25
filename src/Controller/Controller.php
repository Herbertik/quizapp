<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FrontType;
use App\Form\LoginType;
use App\Form\QuizesType;
use App\Form\RegisterType;
use App\Model\Question\AnswerData;
use App\Model\Question\InputType;
use App\Model\Question\Login\LoginData;
use App\Model\Question\QuestionData;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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

#[Route(path: '/mainMenu',name: 'mainMenu', methods: [Request::METHOD_GET])]
    public function mainMenu(UserInterface $user, Security $security, Request $request, QuizRepository $quizRepository): Response
    {
        if ($security->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(QuizesType::class);
        $quizzes = $quizRepository->findByExampleField(0);
        foreach ($quizzes as $quiz) {

            $form->add($quiz->getName(), ButtonType::class);
        }

        $form->handleRequest($request);

        return $this->render('index.html.twig',
        [
            'form' => $form->createView(),
        ]);
    }
}


