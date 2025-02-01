<?php

namespace App\Command;

use App\Entity\Kartenn;
use App\Repository\KartennRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-kartennou',
    description: 'ENporzhian ar c\'hartennoù',
)]
class ImportKartennouCommand extends Command
{
    const URL = 'https://api.nakala.fr/collections/10.34847%2Fnkl.3fedc636/datas';
    const TITLE_URI = 'http://nakala.fr/terms#title';

    const REGEX = '/Carte n°(\d+) "(.+)" du Nouvel Atlas Linguistique de Basse-Bretagne \(NALBB\)/';

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private KartennRepository $kartennRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $batchSize = 10;
        $batchCounter = 0;

        $response = $this->client->request(
            'GET',
            self::URL.'?page=1&limit=25'
        );

        $this->kartennRepository->deleteAllKartennou();

        $jsonContent = $response->getContent();
        $content = json_decode($jsonContent, true);
        foreach($content['data'] as $data) {
            $kartenn = $this->getKartenn($data);
            $this->entityManager->persist($kartenn);
            $batchCounter++;
            if ($batchCounter > $batchSize) {
                $this->entityManager->flush();
            }
        }

        for ($i = 2; $i <= $content['lastPage']; $i++) {
            $response = $this->client->request(
                'GET',
                self::URL.'?page='.$i.'&limit=25'
            );
            $jsonContent = $response->getContent();
            $content = json_decode($jsonContent, true);
            foreach($content['data'] as $data) {
                $kartenn = $this->getKartenn($data);
                $this->entityManager->persist($kartenn);
                $batchCounter++;
                if ($batchCounter > $batchSize) {
                    $this->entityManager->flush();
                }
            }
        }

        $io->success('Yac\'hou');

        return Command::SUCCESS;
    }

    public function extractRealName(string $name) {
        $replacement = '${2}';
        return preg_replace(self::REGEX, $replacement, $name);
    }
    public function extractNumber(string $name) {
        $replacement = '${1}';
        return preg_replace(self::REGEX, $replacement, $name);
    }

    public function getKartenn($data) {
        $kartenn = new Kartenn();
        foreach($data['metas'] as $meta) {
            if ($meta['propertyUri'] === self::TITLE_URI) {
                $kartenn->setAnv($this->extractRealName($meta['value']));
            }
            if ($meta['propertyUri'] === self::TITLE_URI) {
                $kartenn->setNiverenn($this->extractNumber($meta['value']));
            }
        }
        foreach($data['files'] as $file) {
            if (array_key_exists('extension', $file)) {
                if ($file['extension'] === 'pdf') {
                    $kartenn->setPdf($file['name']);
                }
                if ($file['extension'] === 'png') {
                    $kartenn->setPng($file['name']);
                }
            }
        }
        $kartenn->setUrl($data['uri']);
        return $kartenn;
    }
}
