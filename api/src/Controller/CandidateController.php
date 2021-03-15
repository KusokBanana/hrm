<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\CandidateStatuses;
use App\Mutation\CandidateFactory;
use App\Repository\CandidateRepository;
use App\Repository\UserRepository;
use Assert\Assert;
use Assert\InvalidArgumentException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CandidateController extends AbstractController
{
    private CandidateRepository $candidateRepository;
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private CandidateFactory $candidateFactory;
    private UserRepository $userRepository;

    public function __construct(
        CandidateRepository $candidateRepository,
        EntityManagerInterface $entityManager,
        Connection $connection,
        CandidateFactory $candidateFactory,
        UserRepository $userRepository
    )
    {
        $this->candidateRepository = $candidateRepository;
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->candidateFactory = $candidateFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/candidates", name="candidates", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('candidate')
            ->from(Candidate::class, 'candidate')
            ->addOrderBy('candidate.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('skills')) {
            $queryBuilder
                ->join('candidate.skills', 'candidate_skill')
                ->andWhere($expr->in('IDENTITY(candidate_skill.skill)', ':skills'))
                ->setParameter('skills', $request->query->get('skills'));
        }

        if ($request->query->has('status')) {
            CandidateStatuses::validate($request->query->get('status'));

            $queryBuilder
                ->andWhere($expr->in('candidate.status', ':status'))
                ->setParameter('status', $request->query->get('status'));
        }

        return $this->json([
            'data' => $queryBuilder->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/candidates/{id}", name="candidate", methods={"GET"})
     * @param int $id
     *
     * @return Response
     */
    public function candidate(int $id): Response
    {
        $candidate = $this->candidateRepository->findOneBy(['id' => $id]);

        if (!$candidate instanceof Candidate) {
            return new Response(sprintf('Unknown candidate with id "%s"', $id), Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $candidate,
        ]);
    }

    /**
     * @Route("/candidates", name="create_candidate", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $candidate = null;
        try {
            $parameters = $this->getRequestData(
                $request,
                ['skills', 'name', 'sex', 'city', 'birth_date', 'title', 'salary', 'education_history', 'experience', 'languages', 'about', 'status']
            );

            $this->entityManager->transactional(function() use (&$candidate, $parameters, $user) {
                $candidate = $this->candidateFactory->create($parameters);
                $this->userRepository->setCandidate($user, $candidate);
            });
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($candidate);

        return $this->json([
            'data' => $candidate,
        ]);
    }

    /**
     * @Route("/candidates/{id}", name="update_candidate", methods={"PATCH"})
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $candidate = $this->candidateRepository->findOneBy(['id' => $id]);

        if (!$candidate instanceof Candidate) {
            return new Response(sprintf('Unknown candidate with id "%s"', $id), Response::HTTP_NOT_FOUND);
        }

        if (!$this->getUser()->getCandidate() instanceof Candidate || $this->getUser()->getCandidate()->getId() !== $id) {
            return new Response('You are not allowed to update this candidate', Response::HTTP_FORBIDDEN);
        }

        try {
            $parameters = $this->getRequestData(
                $request,
                ['skills', 'name', 'sex', 'city', 'birth_date', 'title', 'salary', 'education_history', 'experience', 'languages', 'about', 'status']
            );

            Assert::that($parameters->count())->greaterThan(0);

            $this->entityManager->transactional(function() use ($candidate, $parameters) {
                $this->candidateFactory->update($candidate, $parameters);
            });
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($candidate);

        return $this->json([
            'data' => $candidate,
        ]);
    }
}
