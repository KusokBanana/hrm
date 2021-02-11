<?php

namespace App\Command;

use App\Repository\SkillRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateCandidateMostRelevantCommand extends Command
{
    protected static $defaultName = 'app:update-candidate-most-relevant';

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
        $this->connection->executeStatement(
            'update candidate c
                 set most_relevant_id = (select r.id from relevance r where r.candidate_id = c.id order by fit DESC limit 1);'
        );

        return Command::SUCCESS;
    }
}
