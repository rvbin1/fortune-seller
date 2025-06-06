<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    private const COPPER = 'Copper';
    private const SILVER = 'Silver';
    private const GOLD = 'Gold';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column]
    private ?int $gw2Id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Recipes>
     */
    #[ORM\OneToMany(targetEntity: Recipes::class, mappedBy: 'outputItem')]
    private Collection $producedRecipes;

    #[ORM\Column]
    private ?bool $sellable = null;

    /**
     * @var array<int, array{attribute: string}>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $attributes = null;

    #[ORM\Column(nullable: true)]
    private ?bool $craftable = null;

    /**
     * @var Collection<int, MysticForge>
     */
    #[ORM\OneToMany(targetEntity: MysticForge::class, mappedBy: 'outputItem')]
    private Collection $producedMysticForges;

    /**
     * @var Collection<int, MysticForgeIngredients>
     */
    #[ORM\OneToMany(targetEntity: MysticForgeIngredients::class, mappedBy: 'ingredientItem')]
    private Collection $usedInMysticForgeIngredients;

    /**
     * @var Collection<int, RecipeIngredients>
     */
    #[ORM\OneToMany(targetEntity: RecipeIngredients::class, mappedBy: 'ingredient')]
    private Collection $usedInRecipeIngredients;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wikiUrl = null;

    public function __construct()
    {
        $this->producedRecipes = new ArrayCollection();
        $this->producedMysticForges = new ArrayCollection();
        $this->usedInMysticForgeIngredients = new ArrayCollection();
        $this->usedInRecipeIngredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGw2Id(): ?int
    {
        return $this->gw2Id;
    }

    public function setGw2Id(int $gw2Id): self
    {
        $this->gw2Id = $gw2Id;
        return $this;
    }

    public function getPicUrl(): ?string
    {
        return $this->picUrl;
    }

    public function setPicUrl(?string $picUrl): self
    {
        $this->picUrl = $picUrl;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Recipes>
     */
    public function getProducedRecipes(): Collection
    {
        return $this->producedRecipes;
    }

    public function addProducedRecipe(Recipes $recipe): self
    {
        if (!$this->producedRecipes->contains($recipe)) {
            $this->producedRecipes->add($recipe);
            $recipe->setOutputItem($this);
        }
        return $this;
    }

    public function removeProducedRecipe(Recipes $recipe): self
    {
        if ($this->producedRecipes->removeElement($recipe)) {
            if ($recipe->getOutputItem() === $this) {
                $recipe->setOutputItem(null);
            }
        }
        return $this;
    }

    public function isSellable(): ?bool
    {
        return $this->sellable;
    }

    public function setSellable(bool $sellable): self
    {
        $this->sellable = $sellable;
        return $this;
    }

    /**
     * @return array<int, array{attribute: string}>
     */
    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }

    /**
     * @param array<int, array{attribute: string}>|null $attributes
     */
    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function isCraftable(): ?bool
    {
        return $this->craftable;
    }

    public function setCraftable(?bool $craftable): self
    {
        $this->craftable = $craftable;
        return $this;
    }

    /**
     * @return Collection<int, MysticForge>
     */
    public function getProducedMysticForges(): Collection
    {
        return $this->producedMysticForges;
    }

    public function addProducedMysticForge(MysticForge $mysticForge): self
    {
        if (!$this->producedMysticForges->contains($mysticForge)) {
            $this->producedMysticForges->add($mysticForge);
            $mysticForge->setOutputItem($this);
        }
        return $this;
    }

    public function removeProducedMysticForge(MysticForge $mysticForge): self
    {
        if ($this->producedMysticForges->removeElement($mysticForge)) {
            if ($mysticForge->getOutputItem() === $this) {
                $mysticForge->setOutputItem(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, MysticForgeIngredients>
     */
    public function getUsedInMysticForgeIngredients(): Collection
    {
        return $this->usedInMysticForgeIngredients;
    }

    public function addUsedInMysticForgeIngredient(MysticForgeIngredients $ingredient): self
    {
        if (!$this->usedInMysticForgeIngredients->contains($ingredient)) {
            $this->usedInMysticForgeIngredients->add($ingredient);
            $ingredient->setIngredientItem($this);
        }
        return $this;
    }

    public function removeUsedInMysticForgeIngredient(MysticForgeIngredients $ingredient): self
    {
        if ($this->usedInMysticForgeIngredients->removeElement($ingredient)) {
            if ($ingredient->getIngredientItem() === $this) {
                $ingredient->setIngredientItem(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, RecipeIngredients>
     */
    public function getUsedInRecipeIngredients(): Collection
    {
        return $this->usedInRecipeIngredients;
    }

    public function addUsedInRecipeIngredient(RecipeIngredients $ingredient): self
    {
        if (!$this->usedInRecipeIngredients->contains($ingredient)) {
            $this->usedInRecipeIngredients->add($ingredient);
        }
        return $this;
    }

    public function removeUsedInRecipeIngredient(RecipeIngredients $ingredient): self
    {
        $this->usedInRecipeIngredients->removeElement($ingredient);
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getAttributeNames(): string
    {
        $attributeNames = [];
        foreach ($this->getAttributes() as $attribute) {
                $attributeNames[] = $attribute['attribute'];
        }
        return implode(', ', $attributeNames);
    }

    public function getConvertedPrice(): string
    {
        $price = $this->getPrice();
        if ($price === null) {
            return 'not sellable';
        }
        $price = (int)$price;

        $gold = intdiv($price, 10000);
        $silver = intdiv($price % 10000, 100);
        $copper = $price % 100;

        $parts = [];
        if ($gold > 0) {
            $parts[] = $gold . ' ' . self::GOLD;
        }
        if ($silver > 0) {
            $parts[] = $silver . ' ' . self::SILVER;
        }
        if ($copper > 0 || empty($parts)) {
            $parts[] = $copper . ' ' . self::COPPER;
        }
        if ($this->sellable === false) {
            return 'not sellable';
        }
        return implode(', ', $parts);
    }

    public function getWikiUrl(): ?string
    {
        return $this->wikiUrl;
    }

    public function setWikiUrl(?string $wikiUrl): static
    {
        $this->wikiUrl = $wikiUrl;

        return $this;
    }
}
