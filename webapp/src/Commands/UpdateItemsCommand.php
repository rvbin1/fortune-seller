<?php

namespace App\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'update:items')]
class UpdateItemsCommand extends Command
{
    private const ITEMS_JSON = 'items.json';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Ruft die Item-Daten aus einer JSON-Datei im Public-Verzeichnis ab und speichert sie in der Datenbank.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Lese die Item-Daten aus der JSON-Datei...');

        $filePath = $this->kernel->getProjectDir() . '/public/json/' . self::ITEMS_JSON;

        if (!$this->filesystem->exists($filePath)) {
            $output->writeln(sprintf('<error>Datei %s existiert nicht.</error>', $filePath));
            return Command::FAILURE;
        }

        // Dateiinhalt einlesen
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            $output->writeln(sprintf('<error>Fehler beim Auslesen der Datei %s.</error>', $filePath));
            return Command::FAILURE;
        }

        try {
            /** @var Item[] $items */
            $items = $this->serializer->deserialize($jsonContent, Item::class . '[]', 'json');
        } catch (\Exception $e) {
            $output->writeln('<error>Fehler beim Deserialisieren der JSON-Daten: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        foreach ($items as $item) {
            $this->entityManager->persist($item);
        }
        $this->entityManager->flush();

        $output->writeln('<info>Item-Daten erfolgreich abgerufen und in der Datenbank gespeichert!</info>');
        return Command::SUCCESS;
    }
}
