<?php

namespace App\Commands;

use App\Entity\Item;
use App\Entity\Recipes;
use App\Entity\RecipeIngredients;
use App\Entity\MysticForge;
use App\Entity\MysticForgeIngredients;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'update:database')]
class UpdateDatabaseFromJsonCommand extends Command
{
    private const string ITEMS_JSON = 'items.json';
    private const string RECIPE_JSON = 'recipe.json';
    private const string MYSTICFORGE_JSON = 'mysticForge.json';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem             $filesystem,
        private readonly SerializerInterface    $serializer,
        private readonly KernelInterface        $kernel
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Verarbeitet Items, Rezepte und MysticForge in dieser Reihenfolge.')
            ->setHelp('Zuerst werden Items aktualisiert, danach Rezepte und zuletzt MysticForge. Jeder Schritt ist in eine eigene Methode ausgelagert.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var int[] $missingItemsReported */
        $missingItemsReported = [];
        /** @var int[] $missingIngredientsReported */
        $missingIngredientsReported = [];

        $this->processItems($output);
        $this->processRecipes($output, $missingItemsReported, $missingIngredientsReported);
        $this->processMysticForge($output, $missingItemsReported, $missingIngredientsReported);
        $this->updateCraftableFlags($output);

        $output->writeln('<info>Kombinierter Update erfolgreich abgeschlossen!</info>');
        return Command::SUCCESS;
    }

    /**
     * Liest die Items aus der JSON-Datei und aktualisiert bzw. fügt sie ein.
     */
    private function processItems(OutputInterface $output): void
    {
        $output->writeln('Lese Item-Daten aus der JSON-Datei...');
        $itemsFilePath = $this->kernel->getProjectDir() . '/public/json/' . self::ITEMS_JSON;
        if (!$this->filesystem->exists($itemsFilePath)) {
            $output->writeln(sprintf('<error>Datei %s existiert nicht.</error>', $itemsFilePath));
            return;
        }
        $jsonContent = file_get_contents($itemsFilePath);
        if ($jsonContent === false) {
            $output->writeln(sprintf('<error>Fehler beim Auslesen der Datei %s.</error>', $itemsFilePath));
            return;
        }
        try {
            /** @var Item[] $itemsFromJson */
            $itemsFromJson = $this->serializer->deserialize($jsonContent, Item::class . '[]', 'json');
        } catch (\Exception $e) {
            $output->writeln('<error>Fehler beim Deserialisieren der JSON-Daten (Items): ' . $e->getMessage() . '</error>');
            return;
        }

        $output->writeln('Verarbeite Items...');
        foreach ($itemsFromJson as $newItem) {
            $existingItem = $this->entityManager->getRepository(Item::class)
                ->findOneBy(['gw2Id' => $newItem->getGw2Id()]);
            if ($existingItem) {
                if ($this->hasItemChanged($existingItem, $newItem)) {
                    $this->entityManager->persist($existingItem);
                    $output->writeln(sprintf('Aktualisiere Item %s (gw2Id: %d).', $newItem->getName(), $newItem->getGw2Id()));
                }
            } else {
                $newItem->setCraftable(false);
                $this->entityManager->persist($newItem);
                $output->writeln(sprintf('Füge neues Item %s (gw2Id: %d) hinzu.', $newItem->getName(), $newItem->getGw2Id()));
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param OutputInterface $output
     * @param int[]           &$missingItemsReported
     * @param int[]           &$missingIngredientsReported
     */
    private function processRecipes(OutputInterface $output, array &$missingItemsReported, array &$missingIngredientsReported): void
    {
        $output->writeln('Lese Rezept-Daten aus der JSON-Datei...');
        $recipeFilePath = $this->kernel->getProjectDir() . '/public/json/' . self::RECIPE_JSON;
        if (!$this->filesystem->exists($recipeFilePath)) {
            $output->writeln(sprintf('<error>Datei %s existiert nicht.</error>', $recipeFilePath));
            return;
        }
        $jsonContent = file_get_contents($recipeFilePath);
        if ($jsonContent === false) {
            $output->writeln(sprintf('<error>Fehler beim Auslesen der Datei %s.</error>', $recipeFilePath));
            return;
        }
        $recipesData = json_decode($jsonContent, true);
        if ($recipesData === null) {
            $output->writeln('<error>Fehler beim Dekodieren der JSON-Daten (Recipes).</error>');
            return;
        }
        /**
         * @var array<int, array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>}> $recipesData
         */
        $output->writeln('Verarbeite Rezepte...');
        foreach ($recipesData as $recipeData) {
            $outputItem = $this->entityManager->getRepository(Item::class)
                ->findOneBy(['gw2Id' => $recipeData['output_item_id']]);
            if (!$outputItem) {
                if (!in_array($recipeData['output_item_id'], $missingItemsReported, true)) {
                    $output->writeln(sprintf(
                        '<error>Item mit gw2Id %d nicht gefunden. Überspringe Rezept.</error>',
                        (int)$recipeData['output_item_id']
                    ));
                    $missingItemsReported[] = $recipeData['output_item_id'];
                }
                continue;
            }

            $existingRecipe = $this->entityManager->getRepository(Recipes::class)
                ->findOneBy(['gw2RecipeId' => $recipeData['gw2_id']]);
            if ($existingRecipe) {
                if ($this->hasRecipeChanged($existingRecipe, $recipeData)) {
                    $this->updateRecipeFrom($existingRecipe, $recipeData, $output, $missingIngredientsReported);
                    $output->writeln(sprintf('Aktualisiere Rezept mit gw2_id %d.', (int)$recipeData['gw2_id']));
                }
            } else {
                $recipe = new Recipes();
                $recipe->setGw2RecipeId($recipeData['gw2_id']);
                $recipe->setOutputItem($outputItem);
                $outputItem->addProducedRecipe($recipe);
                $outputItem->setCraftable(true);
                if (isset($recipeData['ingredients'])) {
                    /** @var array<int, array{item_id: int, count: int}> $recipeDataIngredients */
                    $recipeDataIngredients = $recipeData['ingredients'];
                    foreach ($recipeDataIngredients as $ingredientData) {
                        $ingredient = $this->entityManager->getRepository(Item::class)
                            ->findOneBy(['gw2Id' => $ingredientData['item_id']]);
                        if (!$ingredient) {
                            if (!in_array($ingredientData['item_id'], $missingIngredientsReported, true)) {
                                $output->writeln(sprintf(
                                    '<error>Ingredient mit gw2Id %d nicht gefunden. Überspringe diese Zutat.</error>',
                                    (int)$ingredientData['item_id']
                                ));
                                $missingIngredientsReported[] = $ingredientData['item_id'];
                            }
                            continue;
                        }
                        $recipeIngredient = new RecipeIngredients();
                        $recipeIngredient->setIngredient($ingredient);
                        $recipeIngredient->setQuantity((int)$ingredientData['count']);
                        $recipe->addIngredient($recipeIngredient);
                        $this->entityManager->persist($recipeIngredient);
                    }
                }
                $this->entityManager->persist($recipe);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Liest MysticForge-Daten aus der JSON-Datei und verarbeitet diese.
     *
     * @param OutputInterface $output
     * @param int[]           &$missingItemsReported
     * @param int[]           &$missingIngredientsReported
     */
    private function processMysticForge(OutputInterface $output, array &$missingItemsReported, array &$missingIngredientsReported): void
    {
        $output->writeln('Lese MysticForge-Daten aus der JSON-Datei...');
        $mysticFilePath = $this->kernel->getProjectDir() . '/public/json/' . self::MYSTICFORGE_JSON;
        if (!$this->filesystem->exists($mysticFilePath)) {
            $output->writeln(sprintf('<error>Datei %s existiert nicht.</error>', $mysticFilePath));
            return;
        }
        $jsonContent = file_get_contents($mysticFilePath);
        if ($jsonContent === false) {
            $output->writeln(sprintf('<error>Fehler beim Auslesen der Datei %s.</error>', $mysticFilePath));
            return;
        }
        $mysticData = json_decode($jsonContent, true);
        if ($mysticData === null) {
            $output->writeln('<error>Fehler beim Dekodieren der JSON-Daten (MysticForge).</error>');
            return;
        }
        /**
         * @var array<int, array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>}> $mysticData
         */
        $output->writeln('Verarbeite MysticForge-Rezepte...');
        foreach ($mysticData as $mysticForgeData) {
            $outputItem = $this->entityManager->getRepository(Item::class)
                ->findOneBy(['gw2Id' => $mysticForgeData['output_item_id']]);
            if (!$outputItem) {
                if (!in_array($mysticForgeData['output_item_id'], $missingItemsReported, true)) {
                    $output->writeln(sprintf(
                        '<error>Item mit gw2Id %d nicht gefunden. Überspringe MysticForge-Rezept.</error>',
                        (int)$mysticForgeData['output_item_id']
                    ));
                    $missingItemsReported[] = $mysticForgeData['output_item_id'];
                }
                continue;
            }

            $existingMystic = $this->entityManager->getRepository(MysticForge::class)
                ->findOneBy(['gw2RecipeId' => $mysticForgeData['gw2_id']]);
            if ($existingMystic) {
                if ($this->hasMysticForgeChanged($existingMystic, $mysticForgeData)) {
                    $this->updateMysticForgeFrom($existingMystic, $mysticForgeData, $output, $missingIngredientsReported);
                    $output->writeln(sprintf('Aktualisiere MysticForge-Rezept mit gw2_id %d.', (int)$mysticForgeData['gw2_id']));
                }
            } else {
                $mysticForge = new MysticForge();
                $mysticForge->setGw2RecipeId($mysticForgeData['gw2_id']);
                $mysticForge->setOutputItem($outputItem);
                $outputItem->addProducedMysticForge($mysticForge);
                if (isset($mysticForgeData['ingredients'])) {
                    /** @var array<int, array{item_id: int, count: int}> $mysticForgeIngredientsData */
                    $mysticForgeIngredientsData = $mysticForgeData['ingredients'];
                    foreach ($mysticForgeIngredientsData as $ingredientData) {
                        $ingredient = $this->entityManager->getRepository(Item::class)
                            ->findOneBy(['gw2Id' => $ingredientData['item_id']]);
                        if (!$ingredient) {
                            if (!in_array($ingredientData['item_id'], $missingIngredientsReported, true)) {
                                $output->writeln(sprintf(
                                    '<error>Ingredient mit gw2Id %d nicht gefunden. Überspringe diese Zutat.</error>',
                                    (int)$ingredientData['item_id']
                                ));
                                $missingIngredientsReported[] = $ingredientData['item_id'];
                            }
                            continue;
                        }
                        $mysticForgeIngredient = new MysticForgeIngredients();
                        $mysticForgeIngredient->setIngredientItem($ingredient);
                        $mysticForgeIngredient->setQuantity((int)$ingredientData['count']);
                        $mysticForge->addIngredient($mysticForgeIngredient);
                        $this->entityManager->persist($mysticForgeIngredient);
                    }
                }
                $this->entityManager->persist($mysticForge);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Aktualisiert für alle Items das craftable-Flag basierend auf regulären Recipes.
     * MysticForge-Rezepte werden hierbei nicht berücksichtigt.
     */
    private function updateCraftableFlags(OutputInterface $output): void
    {
        $output->writeln('Aktualisiere craftable-Flags...');
        $allItems = $this->entityManager->getRepository(Item::class)->findAll();
        foreach ($allItems as $item) {
            if ($item->getProducedRecipes()->isEmpty() && $item->isCraftable()) {
                $item->setCraftable(false);
                $this->entityManager->persist($item);
                $output->writeln(sprintf('Setze craftable für Item %s (gw2Id: %d) auf false.', $item->getName(), $item->getGw2Id()));
            }
        }
        $this->entityManager->flush();
    }

    private function hasItemChanged(Item $existing, Item $new): bool
    {
        if ($existing->getName() !== $new->getName()) {
            return true;
        }
        if ($existing->getPicUrl() !== $new->getPicUrl()) {
            return true;
        }
        if ($existing->isSellable() !== $new->isSellable()) {
            return true;
        }
        if ($existing->getAttributes() !== $new->getAttributes()) {
            return true;
        }
        return false;
    }

    /**
     * @param array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>} $recipeData
     */
    private function hasRecipeChanged(Recipes $existing, array $recipeData): bool
    {
        if (($existing->getOutputItem()?->getGw2Id() ?? 0) !== $recipeData['output_item_id']) {
            return true;
        }
        $existingIngredients = [];
        foreach ($existing->getIngredients() as $ingredient) {
            $existingIngredients[$ingredient->getIngredient()?->getGw2Id()] = $ingredient->getQuantity();
        }
        $jsonIngredients = $recipeData['ingredients'] ?? [];
        if (count($existingIngredients) !== count($jsonIngredients)) {
            return true;
        }
        foreach ($jsonIngredients as $ingData) {
            $gw2Id = $ingData['item_id'];
            $qty = $ingData['count'];
            if (!isset($existingIngredients[$gw2Id]) || $existingIngredients[$gw2Id] !== $qty) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>} $recipeData
     * @param int[]                                                                          &$missingIngredientsReported
     */
    private function updateRecipeFrom(Recipes $existing, array $recipeData, OutputInterface $output, array &$missingIngredientsReported): void
    {
        $outputItem = $this->entityManager->getRepository(Item::class)
            ->findOneBy(['gw2Id' => $recipeData['output_item_id']]);
        if ($outputItem && ($existing->getOutputItem()?->getGw2Id() ?? 0) !== $outputItem->getGw2Id()) {
            $existing->setOutputItem($outputItem);
            $outputItem->addProducedRecipe($existing);
            $outputItem->setCraftable(true);
        }
        foreach ($existing->getIngredients() as $oldIngredient) {
            $existing->removeIngredient($oldIngredient);
        }
        if (isset($recipeData['ingredients'])) {
            foreach ($recipeData['ingredients'] as $ingredientData) {
                $ingredient = $this->entityManager->getRepository(Item::class)
                    ->findOneBy(['gw2Id' => $ingredientData['item_id']]);
                if (!$ingredient) {
                    if (!in_array($ingredientData['item_id'], $missingIngredientsReported, true)) {
                        $output->writeln(sprintf(
                            '<error>Ingredient mit gw2Id %d nicht gefunden. Überspringe diese Zutat.</error>',
                            (int)$ingredientData['item_id']
                        ));
                        $missingIngredientsReported[] = $ingredientData['item_id'];
                    }
                    continue;
                }
                $recipeIngredient = new RecipeIngredients();
                $recipeIngredient->setIngredient($ingredient);
                $recipeIngredient->setQuantity((int)$ingredientData['count']);
                $existing->addIngredient($recipeIngredient);
                $this->entityManager->persist($recipeIngredient);
            }
        }
    }

    /**
     * @param array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>} $mysticData
     */
    private function hasMysticForgeChanged(MysticForge $existing, array $mysticData): bool
    {
        if (($existing->getOutputItem()?->getGw2Id() ?? 0) !== $mysticData['output_item_id']) {
            return true;
        }
        $existingIngredients = [];
        foreach ($existing->getIngredients() as $ingredient) {
            $existingIngredients[$ingredient->getIngredientItem()?->getGw2Id()] = $ingredient->getQuantity();
        }
        $jsonIngredients = $mysticData['ingredients'] ?? [];
        if (count($existingIngredients) !== count($jsonIngredients)) {
            return true;
        }
        foreach ($jsonIngredients as $ingData) {
            $gw2Id = $ingData['item_id'];
            $qty = $ingData['count'];
            if (!isset($existingIngredients[$gw2Id]) || $existingIngredients[$gw2Id] !== $qty) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array{output_item_id: int, gw2_id: int, ingredients?: array<int, array{item_id: int, count: int}>} $mysticData
     * @param int[]                                                                          &$missingIngredientsReported
     */
    private function updateMysticForgeFrom(MysticForge $existing, array $mysticData, OutputInterface $output, array &$missingIngredientsReported): void
    {
        $outputItem = $this->entityManager->getRepository(Item::class)
            ->findOneBy(['gw2Id' => $mysticData['output_item_id']]);
        if ($outputItem && ($existing->getOutputItem()?->getGw2Id() ?? 0) !== $outputItem->getGw2Id()) {
            $existing->setOutputItem($outputItem);
            $outputItem->addProducedMysticForge($existing);
        }
        foreach ($existing->getIngredients() as $oldIngredient) {
            $existing->removeIngredient($oldIngredient);
        }
        if (isset($mysticData['ingredients'])) {
            foreach ($mysticData['ingredients'] as $ingredientData) {
                $ingredient = $this->entityManager->getRepository(Item::class)
                    ->findOneBy(['gw2Id' => $ingredientData['item_id']]);
                if (!$ingredient) {
                    if (!in_array($ingredientData['item_id'], $missingIngredientsReported, true)) {
                        $output->writeln(sprintf(
                            '<error>Ingredient mit gw2Id %d nicht gefunden. Überspringe diese Zutat.</error>',
                            (int)$ingredientData['item_id']
                        ));
                        $missingIngredientsReported[] = $ingredientData['item_id'];
                    }
                    continue;
                }
                $mysticForgeIngredient = new MysticForgeIngredients();
                $mysticForgeIngredient->setIngredientItem($ingredient);
                $mysticForgeIngredient->setQuantity((int)$ingredientData['count']);
                $existing->addIngredient($mysticForgeIngredient);
                $this->entityManager->persist($mysticForgeIngredient);
            }
        }
    }
}
