<?php

namespace App\Command;

use App\Entity\Application;
use App\Entity\Skill;
use App\Entity\Vacancy;
use App\Entity\VacancySkill;
use App\Repository\SkillRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FillVacanciesCommand extends Command
{
    protected static $defaultName = 'app:fill-vacancies';
    private const FILE_PATH = '/var/www/data/vacancies.csv';
//    private const DEPARTMENTS = [
//        'Engineering' ,'Legal' ,'Research and Development' ,'Marketing' ,'Accounting' ,'Legal' ,'Research and Development' ,'Engineering' ,'Accounting' ,'Support' ,'Marketing' ,'Engineering' ,'Business Development' ,'Marketing' ,'Legal' ,'Accounting' ,'Product Management' ,'Human Resources' ,'Human Resources' ,'Legal' ,'Support' ,'Legal' ,'Support' ,'Training' ,'Services' ,'Human Resources' ,'Services' ,'Legal' ,'Training' ,'Training' ,'Accounting' ,'Sales' ,'Research and Development' ,'Marketing' ,'Services' ,'Business Development' ,'Legal' ,'Services' ,'Engineering' ,'Product Management' ,'Support' ,'Business Development' ,'Services' ,'Services' ,'Services' ,'Marketing' ,'Services' ,'Sales' ,'Training' ,'Marketing'
//    ];

    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $client;
    private SkillRepository $skillRepository;
    private FakerGenerator $faker;

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
        $this->faker = FakerFactory::create('ru_RU');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (($handle = fopen(self::FILE_PATH, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $this->createVacancy($data);
            }
            fclose($handle);
        }

        return Command::SUCCESS;
    }

    private function createVacancy(array $data): void
    {
        $id = $data[0];

        if (!is_numeric($id)) {
            return;
        }

        $allSkills = $this->skillRepository->findAll();

        $title = $this->getRandomVacancyTitle();
        $description = trim($data[1]);
//        $department = $this->getDepartment();
        $createdAt = $this->getCreatedAt();

        try {
            $vacancy = new Vacancy($title, $description);
            $reflectionClass = new ReflectionClass('App\Entity\Vacancy');
            $property = $reflectionClass->getProperty('createdAt');
            $property->setAccessible(true);
            $property->setValue($vacancy, $createdAt);
            $property->setAccessible(false);
            $this->entityManager->persist($vacancy);

//            $this->addApplications($vacancy);
            $this->addSkills($vacancy, $allSkills);

            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->recreateEntityManager();
            echo 'Error: ' . $exception->getMessage() . "\n";
        }
    }

    private function getCreatedAt(): \DateTimeInterface
    {
        return $this->faker->dateTime();
//        $from = strtotime('last year');
//        $to = time();
//
//        $value = mt_rand($from, $to);
//
//        return (new \DateTime())->setTimestamp($value);
    }

//    private function addApplications(Vacancy $vacancy): void
//    {
//        $max = mt_rand(-2, 4);
//        for ($i = 0; $i < $max; $i++) {
//            $result = $this->client->request('GET', 'https://api.namefake.com/russian-russia/male/');
//            $application = new Application($result->toArray()['name'], false, $vacancy);
//            $this->entityManager->persist($application);
//            $vacancy->addApplication($application);
//        }
//    }

//    private function getDepartment(): string
//    {
//        $index = array_rand(self::DEPARTMENTS);
//        return self::DEPARTMENTS[$index];
//    }

    /**
     * @param Vacancy $vacancy
     * @param Skill[] $skills
     */
    private function addSkills(Vacancy $vacancy, array $skills): void
    {
//        $description = mb_strtolower($vacancy->getDescription());

        foreach ($skills as $skill) {
            $matches = preg_match(sprintf("/\b%s\b/i", preg_quote($skill->getName(), '/')), $vacancy->getDescription());
            if ($matches) {
//            if (str_contains($description, strtolower($skill->getName()))) {
                $level = $this->faker->randomFloat(2, 0, 1) ?: null;
                $vacancySkill = new VacancySkill($vacancy, $skill, $level);
                $this->entityManager->persist($vacancySkill);
            }
        }
    }

    private function recreateEntityManager()
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }

    private function getRandomVacancyTitle(): string
    {
        return $this->connection->executeQuery(
            'select title from candidate c order by RANDOM() limit 1;'
        )->fetchOne();
    }
}
