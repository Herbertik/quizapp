<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FrontType;
use App\Form\LoginType;
use App\Form\RegisterType;
use App\Model\Question\AnswerData;
use App\Model\Question\InputType;
use App\Model\Question\QuestionData;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Model\User\UserService;

class Controller extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    #[Route('/', name: 'index', methods: [Request::METHOD_GET])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) //todo potreba dodelat check if log in
        {
            return $this->render('login.html.twig', [
                'controller_name' => 'Controller',
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute("mainMenu");
    }

    #[Route(path: '/register', name: 'register', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function login(Request $request,UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(RegisterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            $user = UserService::createUserFromForm( $passwordHasher, $form);

            return $this->redirectToRoute("mainMenu");
        }

        return $this->render('register.html.twig', [
            'controller_name' => 'Controller',
            'form' => $form,
        ]);
    }
#[Route(path: '/mainMenu',name: 'mainMenu', methods: [Request::METHOD_GET])]
    public function mainMenu(): Response
    {
        return $this->render('index.html.twig'); // todo main menu je index.html.twig
    }
}