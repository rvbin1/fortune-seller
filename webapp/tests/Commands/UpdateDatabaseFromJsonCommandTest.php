<?php

namespace App\Tests\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;

class UpdateDatabaseFromJsonCommandTest extends Command
{
    protected static $defaultName = 'app:update-database-from-json';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the database from a JSON file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        if (!file_exists($filePath)) {
            $io->error("File not found: " . $filePath);
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        if ($data === null) {
            $io->error("Invalid JSON in file: " . $filePath);
            return Command::FAILURE;
        }

        try {
            $this->processItems($data);
            $this->entityManager->flush();
            $io->success("Database updated successfully.");
        } catch (\Exception $e) {
            $io->error("An error occurred: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processItems(array $data): void
    {
        foreach ($data as $item) {
            if (!isset($item['id'], $item['name'], $item['value'])) {
                continue;
            }

            $entity = $this->entityManager->getRepository(Item::class)->find($item['id']);

            if (!$entity) {
                $entity = new Item();
            }

            $entity->setName($item['name']);
            $entity->setValue($item['value']);

            $this->entityManager->persist($entity);
        }
    }
}
