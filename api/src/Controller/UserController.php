<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private EntityManagerInterface $entityManager;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator
    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $parameters = $this->getRequestData($request, ['login', 'password']);

        $login = $parameters->get('login', '');
        $password = $parameters->get('password', '');

        if (strlen($login) < 3) {
            return new Response('You must provide at least 3 symbols for login', Response::HTTP_BAD_REQUEST);
        }
        if (strlen($login) > 50) {
            return new Response('Maximum symbols for login is 50', Response::HTTP_BAD_REQUEST);
        }
        if (strlen($password) < 3) {
            return new Response('You must provide at least 3 symbols for login', Response::HTTP_BAD_REQUEST);
        }
        if (strlen($password) > 50) {
            return new Response('Maximum symbols for login is 50', Response::HTTP_BAD_REQUEST);
        }

        $exists = $this->userRepository->findOneByLogin($login) instanceof User;

        if ($exists) {
            return new Response(sprintf('User with login "%s" already exists', $login), Response::HTTP_BAD_REQUEST);
        }

        $user = new User($login, []);
        $hashedPassword = $this->userPasswordEncoder->encodePassword($user, $password);
        $token = $this->tokenGenerator->generateToken();
        $user->setPassword($hashedPassword)->setToken($token);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['login' => $user->getUsername(), 'token' => $token]);
    }

    /**
     * @Route("/users/me", name="me", methods={"GET"})
     */
    public function me(): Response
    {
        return $this->json([
            'data' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
