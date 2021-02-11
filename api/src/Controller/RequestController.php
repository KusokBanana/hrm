<?php

namespace App\Controller;

use App\Repository\RequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestController extends AbstractController
{
    private RequestRepository $requestRepository;

    public function __construct(RequestRepository $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @Route("/requests", name="requests")
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);
        $completed = $request->query->get('completed', null);

        $filter = is_null($completed)
            ? []
            : ['completed' => $completed === 'true'];

        $requests = $this->requestRepository->findBy($filter, ['createdAt' => 'DESC'], $limit, $offset);

        return $this->json([
            'data' => $requests,
        ]);
    }
}
