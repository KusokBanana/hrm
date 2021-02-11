<?php

namespace App\Controller;

use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SkillController extends AbstractController
{
    private SkillRepository $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    /**
     * @Route("/skills", name="skills")
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $filter = $request->query->has('query')
            ? ['name' => $request->query->get('query')]
            : [];

        $skills = $this->skillRepository->findBy($filter, ['name' => 'ASC'], $limit, $offset);

        return $this->json([
            'data' => $skills,
        ]);
    }
}
