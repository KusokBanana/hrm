<?php

namespace App\Command;

use App\Entity\Candidate;
use App\Entity\CandidateSex;
use App\Entity\CandidateSkill;
use App\Entity\CandidateStatuses;
use App\Entity\EducationHistory;
use App\Entity\Experience;
use App\Entity\Skill;
use App\Entity\SkillTypes;
use App\Entity\Specialization;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FillCandidatesCommand extends Command
{
    protected static $defaultName = 'app:fill-candidates';
    private const FILE_PATH = '/var/www/data/candidates.json';

    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $client;
    private FakerGenerator $faker;

    private array $allSkills = [];

    public function __construct(
        Connection $connection,
        EntityManagerInterface $entityManager,
        HttpClientInterface $client,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->faker = FakerFactory::create('ru_RU');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = json_decode(file_get_contents(self::FILE_PATH), true);

        $this->entityManager->transactional(function() use ($data) {
            foreach ($data as $item) {
                $candidate = $this->parseCandidate($item);
                $this->entityManager->persist($candidate);
            }
        });

        return Command::SUCCESS;
    }

    private function parseCandidate(array $item): Candidate
    {
        $sex = $item['gender'] === 'Мужчина' ? CandidateSex::MALE : CandidateSex::FEMALE;

        $candidate = new Candidate(
            $this->fetchName($sex),
            $sex,
            trim($item['area']) ?: null,
            $this->parseBirthDate($item),
            $this->parseTitle($item),
//            $this->parseSpecialization($item),
            $this->parseSalary($item),
//            trim($item['education_level']) ?: null,
            $this->parseEducationHistory($item),
            $this->parseExperience($item),
            $this->parseLanguages($item),
            trim($item['skills']) ?: null,
//            $this->parseSkills($item),
            CandidateStatuses::STATUS_ACTIVE_SEARCH
        );

        $skills = $this->parseSkills($item);

        foreach ($skills as $skill) {
            $level = $this->faker->randomFloat(2, 0.01, 0.99);
            $candidateSkill = new CandidateSkill($candidate, $skill, $level);
            $this->entityManager->persist($candidateSkill);
        }

        return $candidate;
    }

    private function parseBirthDate(array $item): ?\DateTime
    {
        $date = $item['birth_date'];

        if (is_null($date)) {
            return null;
        }

        $date = htmlentities($date);
        $date = str_replace('&nbsp;', ' ', $date);
        [$day, $month, $year] = sscanf($date, '%d %s %d');

        $months = [
            'января' => '01',
            'февраля' => '02',
            'марта' => '03',
            'апреля' => '04',
            'мая' => '05',
            'июня' => '06',
            'июля' => '07',
            'августа' => '08',
            'сентября' => '09',
            'октября' => '10',
            'ноября' => '11',
            'декабря' => '12',
        ];

        if (array_key_exists($month, $months)) {
            return new \DateTime(sprintf('%s-%s-%s', $year, $months[$month], $day));
        }

        return new \DateTime($date);
    }

    private function parseTitle(array $item): string
    {
        return trim($item['title']);
    }

    private function parseSalary(array $item): ?int
    {
        $currency = $item['salary']['currency'] ?? null;
        $amount = $item['salary']['amount'] ?? null;

        if (is_null($currency) || is_null($amount)) {
            return null;
        }

        if ($currency === 'руб.' || $currency === 'RUB') {
            return (int) $amount;
        }

        if ($currency === 'USD') {
            return (int) ($amount * 76);
        }

        if ($currency === 'KZT') {
            return (int) ($amount * 0.18);
        }

        throw new \LogicException(sprintf('Unknown salary currency %s', $currency));
    }

    private function parseSkills(array $item): array
    {
        $skills = [];

        foreach ($item['skill_set'] ?: [] as $name) {
            $skill = new Skill($name, SkillTypes::TYPE_HARD);

            if (array_key_exists($skill->getCode(), $this->allSkills)) {
                $skill = $this->allSkills[$skill->getCode()];
            }

            if (array_key_exists($skill->getCode(), $skills)) { // duplicate
                continue;
            }

            $skills[$skill->getCode()] = $skill;
            $this->allSkills[$skill->getCode()] = $skill;
            $this->entityManager->persist($skill);
        }

        return $skills;
    }

    private function parseExperience(array $item): array
    {
        return array_map(
            fn(array $experience) => new Experience(
                $experience['position'],
                $experience['description'],
                \DateTime::createFromFormat('d-m-Y', $experience['start']),
                $experience['end'] ? \DateTime::createFromFormat('d-m-Y', $experience['end']) : null,
            ),
            $item['experience'] ?: []
        );
    }

    private function parseLanguages(array $item): array
    {
        return array_column($item['language'] ?: [], 'name');
    }

    private function parseSpecialization(array $item): array
    {
        return array_map(
            fn($specialization) => new Specialization(
                $specialization['name'],
                $specialization['profarea_name'],
            ),
            $item['specialization'] ?: []
        );
    }

    private function parseEducationHistory(array $item): array
    {
        return array_map(
            fn($specialization) => new EducationHistory(
                $specialization['name'],
                $specialization['organization'],
                $specialization['year'],
            ),
            $item['education'] ?: []
        );
    }

    private function fetchName(string $sex): string
    {
        $sex = $sex === CandidateSex::MALE ? 'male' : 'female';
        $result = $this->client->request('GET', sprintf('https://api.namefake.com/russian-russia/%s/', $sex));
        return $result->toArray()['name'];
    }
}
