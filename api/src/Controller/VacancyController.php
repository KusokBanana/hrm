<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\CandidateStatuses;
use App\Entity\Relevance;
use App\Entity\Vacancy;
use App\Repository\VacancyRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class VacancyController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private VacancyRepository $vacancyRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Connection $connection,
        VacancyRepository $vacancyRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->vacancyRepository = $vacancyRepository;
    }

    /**
     * @Route("/vacancies", name="vacancies")
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $vacancies = $this->findVacancies($limit, $offset);

        return $this->json([
            'data' => array_map(
                fn(array $vacancy) => array_merge(
                    $vacancy,
                    ['skills' => $this->findSkills($vacancy['id'])]
                ),
                $vacancies
            ),
        ]);
    }

    /**
     * @Route("/vacancies/{id}", name="vacancy")
     * @param int     $id
     *
     * @return Response
     */
    public function vacancy(int $id): Response
    {
        $vacancy = $this->vacancyRepository->findOneBy(['id' => $id]);

        if (!$vacancy instanceof Vacancy) {
            throw new NotFoundHttpException('Vacancy not found');
        }

        return $this->json($vacancy);
    }

    /**
     * @Route("/vacancies/{id}/relevance", name="vacancy-relevance")
     * @param int     $id
     *
     * @param Request $request
     *
     * @return Response
     */
    public function vacancyCandidates(int $id, Request $request): Response
    {
        $vacancy = $this->vacancyRepository->findOneBy(['id' => $id]);

        if (!$vacancy instanceof Vacancy) {
            throw new NotFoundHttpException('Vacancy not found');
        }

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('relevance')
            ->addSelect('candidate')
            ->from(Relevance::class, 'relevance')
            ->join('relevance.candidate', 'candidate')
            ->join('relevance.vacancy', 'vacancy')
            ->orderBy('relevance.fit', 'DESC')
            ->where($expr->eq('vacancy.id', ':id'))
            ->setParameter('id', $vacancy->getId());

        if ($request->query->has('status')) {
            CandidateStatuses::validate($request->query->get('status'));

            $queryBuilder
                ->andWhere($expr->eq('candidate.status', ':status'))
                ->setParameter('status', $request->query->get('status'));
        }

        if ($request->query->has('fit_from')) {
            $queryBuilder
                ->andWhere($expr->gte('relevance.fit', ':from'))
                ->setParameter('from', $request->query->get('fit_from'));
        }

        if ($request->query->has('fit_to')) {
            $queryBuilder
                ->andWhere($expr->lte('relevance.fit', ':to'))
                ->setParameter('to', $request->query->get('fit_to'));
        }

        if ($request->query->has('skills')) {
            $queryBuilder
                ->join('candidate.skills', 'skills')
                ->andWhere($expr->in('skills.id', ':skills'))
                ->setParameter('skills', $request->query->get('skills'));
        }

        $data = array_map(
            fn(Relevance $relevance) => [
                'fit' => $relevance->getFit(),
                'candidate' => $relevance->getCandidate(),
            ],
            $queryBuilder->getQuery()->getResult()
        );

        return $this->json([
            'data' => $data,
        ]);
    }

    private function findVacancies(int $limit, int $offset): array
    {
        $where = '';
        $params = [];

        return $this->connection->executeQuery(
            sprintf(
                'select v.*, 
                count(a.id) filter (where a.processed = true) as processed_count, 
                count(a.id) filter (where a.processed = false) as unprocessed_count
                from vacancy v 
                join application a on a.vacancy_id = v.id
                %s
                group by v.id
                order by v.created_at desc, unprocessed_count DESC
                LIMIT %d OFFSET %d',
                $where, $limit, $offset
            ),
            $params
        )->fetchAllAssociative();
    }

    private function findSkills(int $vacancyId): array
    {
        return $this->connection->executeQuery(
            sprintf(
                'select s.id, s.name
                from vacancy_skill vs 
                join skill s on vs.skill_id = s.id
                where vs.vacancy_id = %d
                order by s.name ASC',
                $vacancyId
            )
        )->fetchAllAssociative();
    }
}
