<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $gw2Id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pic_url = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Recipes>
     */
    #[ORM\OneToMany(targetEntity: Recipes::class, mappedBy: 'outputItem')]
    private Collection $recipes;

    #[ORM\Column]
    private ?bool $sellable = null;

    #[ORM\Column(nullable: true)]
    private ?array $attributes = null;

    #[ORM\Column]
    private ?bool $craftable = null;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
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
        return $this->pic_url;
    }

    public function setPicUrl(?string $pic_url): self
    {
        $this->pic_url = $pic_url;
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
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipes $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setOutputItem($this);
        }
        return $this;
    }

    public function removeRecipe(Recipes $recipe): self
    {
        if ($this->recipes->removeElement($recipe)) {
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

    public function setSellable(bool $sellable): static
    {
        $this->sellable = $sellable;

        return $this;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributeName(): string
    {
        $attributeNames = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributeNames[] = $attribute['attribute'];
        }
        return implode(', ', $attributeNames);
    }

    public function isCraftable(): ?bool
    {
        return $this->craftable;
    }

    public function setCraftable(bool $craftable): static
    {
        $this->craftable = $craftable;

        return $this;
    }
}
