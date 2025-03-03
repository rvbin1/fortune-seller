<?php

namespace App\Commands;

use App\Entity\Recipes;
use App\Entity\RecipeIngredients;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'update:recipes')]
class UpdateRecipesCommand extends Command
{
    private const RECIPE_JSON = 'recipe.json';

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
        $this->setDescription('Liest Rezepte inklusive Zutaten aus einer JSON-Datei im Public-Verzeichnis ein und speichert sie in der Datenbank.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Lese Rezepte aus der JSON-Datei...');

        $filePath = $this->kernel->getProjectDir() . '/public/json/' . self::RECIPE_JSON;
        if (!$this->filesystem->exists($filePath)) {
            $output->writeln(sprintf('<error>Datei %s existiert nicht.</error>', $filePath));
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            $output->writeln(sprintf('<error>Fehler beim Auslesen der Datei %s.</error>', $filePath));
            return Command::FAILURE;
        }

        try {
            $data = $this->serializer->decode($jsonContent, 'json');
        } catch (\Exception $e) {
            $output->writeln('<error>Fehler beim Dekodieren der JSON-Daten: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        foreach ($data as $recipeData) {
            $recipe = new Recipes();

            $outputItem = $this->entityManager->getRepository(Item::class)
                ->findOneBy(['gw2Id' => $recipeData['output_item_id']]);

            if (!$outputItem) {
                $output->writeln(sprintf(
                    '<error>Item mit ID %d nicht gefunden. Überspringe dieses Rezept.</error>',
                    $recipeData['output_item_id']
                ));
                continue;
            }

            if (isset($recipeData['gw2_id'])) {
                $recipe->setGw2RecipeId($recipeData['gw2_id']);
            }

            $recipe->setOutputItem($outputItem);
            $outputItem->addRecipe($recipe);
            $outputItem->setCraftable(true);



            if (isset($recipeData['ingredients']) && is_array($recipeData['ingredients'])) {
                foreach ($recipeData['ingredients'] as $ingredientData) {
                    $ingredient = $this->entityManager->getRepository(Item::class)
                        ->findOneBy(['gw2Id' => $ingredientData['item_id']]);

                    if (!$ingredient) {
                        $output->writeln(sprintf(
                            '<error>Ingredient mit ID %d nicht gefunden. Überspringe diese Zutat.</error>',
                            $ingredientData['item_id']
                        ));
                        continue;
                    }

                    $recipeIngredient = new RecipeIngredients();
                    $recipeIngredient->setIngredient($ingredient);
                    $recipeIngredient->setQuantity($ingredientData['count']);

                    $recipe->addIngredient($recipeIngredient);
                }
            }

            $this->entityManager->persist($recipe);
        }

        $items = $this->entityManager->getRepository(Item::class)->createQueryBuilder('i')
            ->where('i.craftable != :craftable')
            ->setParameter('craftable', false)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            if (!$item instanceof Item) continue;
            $item->setCraftable(false);
        }

        $this->entityManager->flush();
        $output->writeln('<info>Rezepte und deren Zutaten wurden erfolgreich gespeichert!</info>');

        return Command::SUCCESS;
    }
}
