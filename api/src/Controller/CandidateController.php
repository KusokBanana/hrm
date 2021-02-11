<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\CandidateStatuses;
use App\Repository\CandidateRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CandidateController extends AbstractController
{
    private CandidateRepository $candidateRepository;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    public function __construct(
        CandidateRepository $candidateRepository,
        EntityManagerInterface $entityManager,
        Connection $connection
    )
    {
        $this->candidateRepository = $candidateRepository;
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    /**
     * @Route("/candidates", name="candidates")
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('candidate')
            ->from(Candidate::class, 'candidate')
            ->join('candidate.mostRelevant', 'most_relevant')
            ->orderBy('most_relevant.fit', 'DESC')
            ->addOrderBy('candidate.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('skills')) {
            $queryBuilder
                ->join('candidate.skills', 'skills')
                ->andWhere($expr->in('skills.id', ':skills'))
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
     * @Route("/candidates/{id}", name="candidate")
     * @param int $id
     *
     * @return Response
     */
    public function candidate(int $id): Response
    {
        $data = $this->candidateRepository->findOneBy(['id' => $id]);

        return $this->json([
            'data' => $data,
        ]);
    }
}
