<?php

namespace App\Controller;

use App\Entity\CandidateStatuses;
use App\Entity\Relevance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RelevanceController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/relevance/vacancy/{vacancyId}", name="relevance_vacancy", methods={"GET"})
     */
    public function index(Request $request, int $vacancyId): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('relevance')
            ->from(Relevance::class, 'relevance')
            ->join('relevance.vacancy', 'vacancy')
            ->join('relevance.candidate', 'candidate')
            ->where($expr->eq('vacancy.id', ':vacancy_id'))
            ->setParameter('vacancy_id', $vacancyId)
            ->addOrderBy('relevance.fit', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('fit_from')) {
            $queryBuilder
                ->andWhere($expr->gte('relevance.fit', ':fit_from'))
                ->setParameter('fit_from', $request->query->get('fit_from'));
        }

        if ($request->query->has('fit_to')) {
            $queryBuilder
                ->andWhere($expr->lte('relevance.fit', ':fit_to'))
                ->setParameter('fit_to', $request->query->get('fit_to'));
        }

        if ($request->query->has('skills')) {
            $queryBuilder
                ->join('candidate.skills', 'candidate_skills')
                ->join('candidate_skills.skill', 'skill')
                ->andWhere($expr->in('skill.code', ':skills'))
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
}
