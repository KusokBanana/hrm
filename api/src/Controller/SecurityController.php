<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\TokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private TokenGeneratorInterface $tokenGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $userPasswordEncoder,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request): Response
    {
        $parameters = $this->getRequestData($request, ['login', 'password']);

        $login = $parameters->get('login', '');
        $password = $parameters->get('password', '');

        $user = $this->userRepository->findOneByLogin($login);

        if (!$user instanceof User) {
            return new Response(sprintf('User with login "%s" not found', $login), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $valid = $this->userPasswordEncoder->isPasswordValid($user, $password);

        if (!$valid) {
            return new Response('Incorrect password', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $this->tokenGenerator->generateToken();
        $user->setToken($token);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['login' => $login, 'token' => $token]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
