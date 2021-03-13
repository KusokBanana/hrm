<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Entity\SkillTypes;
use App\Mutation\SkillFactory;
use App\Repository\SkillRepository;
use Assert\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SkillController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SkillFactory $skillFactory;
    private SkillRepository $skillRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SkillFactory $skillFactory,
        SkillRepository $skillRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->skillFactory  = $skillFactory;
        $this->skillRepository = $skillRepository;
    }

    /**
     * @Route("/skills", name="skills", methods={"GET"})
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

    /**
     * @Route("/skills", name="create_skill", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
//        $user = $this->getUser();
        try {
            $parameters = $this->getRequestData($request, ['name', 'type', 'parent_code']);
            $skill = $this->skillFactory->create($parameters);
            $this->entityManager->flush();
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($skill);

        return $this->json([
            'data' => $skill,
        ]);
    }

    /**
     * @Route("/skills/{code}", name="update_skill", methods={"PATCH"})
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     */
    public function update(Request $request, string $code): Response
    {
        $skill = $this->skillRepository->find($code);

        if (!$skill instanceof Skill) {
            return new Response(sprintf('Unknown skill with code "%s"', $code), Response::HTTP_NOT_FOUND);
        }

        try {
            $parameters = $this->getRequestData($request, ['name', 'type', 'parent_code']);
            $this->skillFactory->update($skill, $parameters);
            $this->entityManager->flush();
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($skill);

        return $this->json([
            'data' => $skill,
        ]);
    }

    /**
     * @Route("/skills/{code}", name="delete_skill", methods={"DELETE"})
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     */
    public function delete(Request $request, string $code): Response
    {
        $skill = $this->skillRepository->find($code);

        if (!$skill instanceof Skill) {
            return new Response(sprintf('Unknown skill with code "%s"', $code), Response::HTTP_NOT_FOUND);
        }

        try {
            $this->skillFactory->delete($skill);
            $this->entityManager->flush();
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
