<?php

namespace App\Entity;

use App\Repository\RecipesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipesRepository::class)]
class Recipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'producedRecipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $outputItem = null;

    /**
     * Zutaten der Rezeptur.
     *
     * @var Collection<int, RecipeIngredients>
     */
    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: RecipeIngredients::class, cascade: ['persist', 'remove'])]
    private Collection $ingredients;

    #[ORM\Column]
    private ?int $gw2RecipeId = null;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOutputItem(): ?Item
    {
        return $this->outputItem;
    }

    public function setOutputItem(?Item $outputItem): self
    {
        $this->outputItem = $outputItem;
        return $this;
    }

    /**
     * @return Collection<int, RecipeIngredients>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(RecipeIngredients $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setRecipe($this);
        }
        return $this;
    }

    public function removeIngredient(RecipeIngredients $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            if ($ingredient->getRecipe() === $this) {
                $ingredient->setRecipe(null);
            }
        }
        return $this;
    }

    public function getGw2RecipeId(): ?int
    {
        return $this->gw2RecipeId;
    }

    public function setGw2RecipeId(int $gw2RecipeId): self
    {
        $this->gw2RecipeId = $gw2RecipeId;
        return $this;
    }
}
