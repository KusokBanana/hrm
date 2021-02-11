<?php

namespace App\Command;

use App\Entity\Candidate;
use App\Entity\Relevance;
use App\Entity\Vacancy;
use App\Repository\SkillRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FillRelevanceCommand extends Command
{
    protected static $defaultName = 'app:fill-relevance';
    private const FILE_PATH = '/var/www/data/relevance.csv';

    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $client;
    private SkillRepository $skillRepository;

    public function __construct(
        Connection $connection,
        EntityManagerInterface $entityManager,
        HttpClientInterface $client,
        SkillRepository $skillRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->skillRepository = $skillRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (($handle = fopen(self::FILE_PATH, "r")) !== FALSE) {
            $isHeader = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                $vacancyIndex = $data[0];
                unset($data[0]);

                foreach (array_values($data) as $candidateIndex => $fit) {
                    $this->createRelevance($vacancyIndex, $candidateIndex, (float) $fit);
                }

            }
            fclose($handle);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }

    private function createRelevance(int $vacancyIndex, int $candidateIndex, float $fit): void
    {
        if ($fit === 0) {
            return;
        }

        $vacancy = $this->getVacancyByIndex($vacancyIndex);
        $candidate = $this->getCandidateByIndex($candidateIndex);

        $relevance = new Relevance($candidate, $vacancy, $fit);
        $this->entityManager->persist($relevance);
    }

    private function getVacancyByIndex(int $index): Vacancy
    {
        return $this->entityManager->createQueryBuilder()
            ->select('vacancy')
            ->from(Vacancy::class, 'vacancy')
            ->orderBy('vacancy.id', 'ASC')
            ->setMaxResults(1)
            ->setFirstResult($index)
            ->getQuery()->getOneOrNullResult();
    }

    private function getCandidateByIndex(int $index): Candidate
    {
        return $this->entityManager->createQueryBuilder()
            ->select('candidate')
            ->from(Candidate::class, 'candidate')
            ->orderBy('candidate.id', 'ASC')
            ->setMaxResults(1)
            ->setFirstResult($index)
            ->getQuery()->getOneOrNullResult();
    }
}
