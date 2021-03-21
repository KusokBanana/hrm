<?php

namespace App\Controller;

use App\Entity\Company;
use App\Mutation\CompanyFactory;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Assert\Assert;
use Assert\InvalidArgumentException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    private const MUTATE_FIELDS = ['name', 'description'];

    private CompanyRepository $companyRepository;
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private CompanyFactory $companyFactory;
    private UserRepository $userRepository;

    public function __construct(
        CompanyRepository $companyRepository,
        EntityManagerInterface $entityManager,
        Connection $connection,
        CompanyFactory $companyFactory,
        UserRepository $userRepository
    )
    {
        $this->companyRepository = $companyRepository;
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->companyFactory = $companyFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/companies", name="companies", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $limit = $request->query->get('limit', 50);
        $offset = $request->query->get('offset', 0);

        $expr = $this->entityManager->getExpressionBuilder();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('company')
            ->from(Company::class, 'company')
            ->addOrderBy('company.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($request->query->has('name')) {
            $queryBuilder
                ->andWhere($expr->like('LOWER(company.name)', 'LOWER(:name)'))
                ->setParameter('name', '%' . $request->query->get('name') . '%');
        }

        return $this->json([
            'data' => $queryBuilder->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/companies/{id}", name="company", methods={"GET"})
     * @param int $id
     *
     * @return Response
     */
    public function company(int $id): Response
    {
        $company = $this->companyRepository->find($id);

        if (!$company instanceof Company) {
            return new Response(sprintf('Unknown company with id "%s"', $id), Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $company,
        ]);
    }

    /**
     * @Route("/companies", name="create_company", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $company = null;
        $parameters = $this->getRequestData($request, self::MUTATE_FIELDS, self::MUTATE_FIELDS);

        $duplicate = $this->companyRepository->findOneBy(['name' => $parameters->get('name')]);

        if ($duplicate instanceof Company) {
            throw new ConflictHttpException(sprintf('Company with name "%s" already exists', $duplicate->getName()));
        }

        try {
            $this->entityManager->transactional(function() use (&$company, $parameters) {
                $company = $this->companyFactory->create($this->getUser(), $parameters);
            });
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->refresh($company);

        return $this->json([
            'data' => $company,
        ]);
    }

    /**
     * @Route("/companies/{id}", name="update_company", methods={"PATCH"})
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $parameters = $this->getRequestData($request, self::MUTATE_FIELDS, self::MUTATE_FIELDS);

        $company = $this->companyRepository->find($id);

        if (!$company instanceof Company) {
            throw new NotFoundHttpException(sprintf('Unknown company with id "%s"', $id));
        }

        if ($this->getUser()->getUsername() !== $company->getAuthor()->getUsername()) {
            throw new AccessDeniedHttpException('You are not allowed to update this company');
        }

        $duplicate = $this->companyRepository->findOneBy(['name' => $parameters->get('name')]);

        if ($duplicate instanceof Company && $duplicate->getId() !== $company->getId()) {
            throw new ConflictHttpException(sprintf('Company with name "%s" already exists', $duplicate->getName()));
        }

        try {
            Assert::that($parameters->all())->keyExists('name')->keyExists('description');

            $this->entityManager->transactional(function() use ($company, $parameters) {
                $this->companyFactory->update($this->getUser(), $company, $parameters);
            });
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        $this->entityManager->refresh($company);

        return $this->json([
            'data' => $company,
        ]);
    }

    /**
     * @Route("/companies/{id}", name="delete_company", methods={"DELETE"})
     * @param Request $request
     * @param int  $id
     *
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $company = $this->companyRepository->find($id);

        if (!$company instanceof Company) {
            throw new NotFoundHttpException(sprintf('Unknown company with id "%d"', $id));
        }

        try {
            $this->companyFactory->delete($company);
            $this->entityManager->flush();
        } catch (InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
