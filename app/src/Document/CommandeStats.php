<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'commande_stats')]
class CommandeStats
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'int')]
    private int $commandeId;

    #[ODM\Field(type: 'int')]
    private int $menuId;

    #[ODM\Field(type: 'string')]
    private string $menuTitre;

    #[ODM\Field(type: 'float')]
    private float $prixTotal;

    #[ODM\Field(type: 'int')]
    private int $numPersons;

    #[ODM\Field(type: 'date')]
    private \DateTimeInterface $createdAt;

    public function getId(): ?string { return $this->id; }

    public function getCommandeId(): int { return $this->commandeId; }
    public function setCommandeId(int $commandeId): void { $this->commandeId = $commandeId; }

    public function getMenuId(): int { return $this->menuId; }
    public function setMenuId(int $menuId): void { $this->menuId = $menuId; }

    public function getMenuTitre(): string { return $this->menuTitre; }
    public function setMenuTitre(string $menuTitre): void { $this->menuTitre = $menuTitre; }

    public function getPrixTotal(): float { return $this->prixTotal; }
    public function setPrixTotal(float $prixTotal): void { $this->prixTotal = $prixTotal; }

    public function getNumPersons(): int { return $this->numPersons; }
    public function setNumPersons(int $numPersons): void { $this->numPersons = $numPersons; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): void { $this->createdAt = $createdAt; }
}
