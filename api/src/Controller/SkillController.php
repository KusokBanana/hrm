<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Entity\SkillTypes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SkillController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/skills", name="skills")
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('skill')
            ->from(Skill::class, 'skill')
            ->addOrderBy('skill.code', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('code')) {
            $queryBuilder
                ->andWhere($expr->eq('skill.code', ':code'))
                ->setParameter('code', $request->query->get('code'));
        }

        if ($request->query->has('name')) {
            $queryBuilder
                ->andWhere($expr->like('LOWER(skill.name)', 'LOWER(:name)'))
                ->setParameter('name', '%' . $request->query->get('name') . '%');
        }

        if ($request->query->has('parent_code')) {
            $queryBuilder
                ->join('skill.parent', 'parent')
                ->andWhere($expr->eq('parent.code', ':code'))
                ->setParameter('code', $request->query->get('parent_code'));
        }

        if ($request->query->has('type')) {
            SkillTypes::validate($request->query->get('type'));
            $queryBuilder
                ->andWhere($expr->eq('skill.type', ':type'))
                ->setParameter('type', $request->query->get('type'));
        }

        return $this->json([
            'data' => $queryBuilder->getQuery()->getResult(),
        ]);
    }
}
