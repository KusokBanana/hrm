<?php

namespace App\Controller;

use App\Entity\Vacancy;
use App\Mutation\VacancyFactory;
use App\Repository\UserRepository;
use App\Repository\VacancyRepository;
use Assert\Assert;
use Assert\InvalidArgumentException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VacancyController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private VacancyRepository $vacancyRepository;
    private VacancyFactory $vacancyFactory;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Connection $connection,
        VacancyRepository $vacancyRepository,
        VacancyFactory $vacancyFactory,
        UserRepository $userRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->vacancyRepository = $vacancyRepository;
        $this->vacancyFactory = $vacancyFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/vacancies", name="vacancies", methods={"GET"})
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('vacancy')
            ->from(Vacancy::class, 'vacancy')
            ->addOrderBy('vacancy.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('skills')) {
            $queryBuilder
                ->join('vacancy.skills', 'skills')
                ->andWhere($expr->in('skills.id', ':skills'))
                ->setParameter('skills', $request->query->get('skills'));
        }

        return $this->json([
            'data' => $queryBuilder->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/vacancies/{id}", name="vacancy", methods={"GET"})
     * @param int     $id
     *
     * @return Response
     */
    public function vacancy(int $id): Response
    {
        $vacancy = $this->vacancyRepository->findOneBy(['id' => $id]);

        if (!$vacancy instanceof Vacancy) {
            return new Response(sprintf('Unknown vacancy with id "%s"', $id), Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $vacancy,
        ]);
    }

    /**
     * @Route("/vacancies", name="create_vacancy", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $vacancy = null;
        try {
            $parameters = $this->getRequestData(
                $request,
                ['skills', 'title', 'description']
            );

            $this->entityManager->transactional(function() use (&$vacancy, $parameters, $user) {
                $vacancy = $this->vacancyFactory->create($parameters);
                $this->userRepository->setVacancy($user, $vacancy);
            });
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($vacancy);

        return $this->json([
            'data' => $vacancy,
        ]);
    }

    /**
     * @Route("/vacancies/{id}", name="update_vacancy", methods={"PATCH"})
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $vacancy = $this->vacancyRepository->findOneBy(['id' => $id]);

        if (!$vacancy instanceof Vacancy) {
            return new Response(sprintf('Unknown vacancy with id "%s"', $id), Response::HTTP_NOT_FOUND);
        }

        if (!$this->getUser()->getVacancy() instanceof Vacancy || $this->getUser()->getVacancy()->getId() !== $id) {
            return new Response('You are not allowed to update this candidate', Response::HTTP_FORBIDDEN);
        }

        try {
            $parameters = $this->getRequestData(
                $request,
                ['skills', 'title', 'description']
            );

            Assert::that($parameters->count())->greaterThan(0);

            $this->entityManager->transactional(function() use ($vacancy, $parameters) {
                $this->vacancyFactory->update($vacancy, $parameters);
            });
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($vacancy);

        return $this->json([
            'data' => $vacancy,
        ]);
    }
}
