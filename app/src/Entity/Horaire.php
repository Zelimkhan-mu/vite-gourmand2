<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $jour = null;

    #[ORM\Column(nullable: true)]
    private ?int $ouvertureHeure = null;

    #[ORM\Column(nullable: true)]
    private ?int $ouvertureMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $fermetureHeure = null;

    #[ORM\Column(nullable: true)]
    private ?int $fermetureMinutes = null;

    #[ORM\Column]
    private ?bool $isClosed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getOuvertureHeure(): ?int
    {
        return $this->ouvertureHeure;
    }

    public function setOuvertureHeure(?int $ouvertureHeure): static
    {
        $this->ouvertureHeure = $ouvertureHeure;

        return $this;
    }

    public function getOuvertureMinutes(): ?int
    {
        return $this->ouvertureMinutes;
    }

    public function setOuvertureMinutes(?int $ouvertureMinutes): static
    {
        $this->ouvertureMinutes = $ouvertureMinutes;

        return $this;
    }

    public function getFermetureHeure(): ?int
    {
        return $this->fermetureHeure;
    }

    public function setFermetureHeure(?int $fermetureHeure): static
    {
        $this->fermetureHeure = $fermetureHeure;

        return $this;
    }

    public function getFermetureMinutes(): ?int
    {
        return $this->fermetureMinutes;
    }

    public function setFermetureMinutes(?int $fermetureMinutes): static
    {
        $this->fermetureMinutes = $fermetureMinutes;

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->isClosed;
    }

    public function setClosed(bool $isClosed): static
    {
        $this->isClosed = $isClosed;

        return $this;
    }
}
