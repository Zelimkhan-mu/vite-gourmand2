<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Menu::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\Column(nullable: true)]
    private ?int $numPersons = null;

    #[ORM\Column(type: 'text')]
    private ?string $adresseLivraison = null;

    #[ORM\Column(length: 255)]
    private ?string $villeLivraison = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostalLivraison = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureLivraison = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?string $distanceLivraisonKm = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $prixMenu = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $fraisLivraison = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $prixTotal = null;

    #[ORM\Column(length: 30)]
    private ?string $statutCommande = null;

    #[ORM\Column]
    private ?bool $pretMateriel = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motifAnnulation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $moyenContactAnnulation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, StatutCommandeHistorique>
     */
    #[ORM\OneToMany(targetEntity: StatutCommandeHistorique::class, mappedBy: 'commande', orphanRemoval: true)]
    private Collection $historiqueStatuts;

    public function __construct()
    {
        $this->historiqueStatuts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getNumPersons(): ?int
    {
        return $this->numPersons;
    }

    public function setNumPersons(?int $numPersons): static
    {
        $this->numPersons = $numPersons;

        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(string $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;

        return $this;
    }

    public function getVilleLivraison(): ?string
    {
        return $this->villeLivraison;
    }

    public function setVilleLivraison(string $villeLivraison): static
    {
        $this->villeLivraison = $villeLivraison;

        return $this;
    }

    public function getCodePostalLivraison(): ?string
    {
        return $this->codePostalLivraison;
    }

    public function setCodePostalLivraison(string $codePostalLivraison): static
    {
        $this->codePostalLivraison = $codePostalLivraison;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getHeureLivraison(): ?\DateTimeInterface
    {
        return $this->heureLivraison;
    }

    public function setHeureLivraison(\DateTimeInterface $heureLivraison): static
    {
        $this->heureLivraison = $heureLivraison;

        return $this;
    }

    public function getDistanceLivraisonKm(): ?string
    {
        return $this->distanceLivraisonKm;
    }

    public function setDistanceLivraisonKm(?string $distanceLivraisonKm): static
    {
        $this->distanceLivraisonKm = $distanceLivraisonKm;

        return $this;
    }

    public function getPrixMenu(): ?string
    {
        return $this->prixMenu;
    }

    public function setPrixMenu(string $prixMenu): static
    {
        $this->prixMenu = $prixMenu;

        return $this;
    }

    public function getFraisLivraison(): ?string
    {
        return $this->fraisLivraison;
    }

    public function setFraisLivraison(string $fraisLivraison): static
    {
        $this->fraisLivraison = $fraisLivraison;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getPrixTotal(): ?string
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(string $prixTotal): static
    {
        $this->prixTotal = $prixTotal;

        return $this;
    }

    public function getStatutCommande(): ?string
    {
        return $this->statutCommande;
    }

    public function setStatutCommande(string $statutCommande): static
    {
        $this->statutCommande = $statutCommande;

        return $this;
    }

    public function isPretMateriel(): ?bool
    {
        return $this->pretMateriel;
    }

    public function setPretMateriel(bool $pretMateriel): static
    {
        $this->pretMateriel = $pretMateriel;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function getMoyenContactAnnulation(): ?string
    {
        return $this->moyenContactAnnulation;
    }

    public function setMoyenContactAnnulation(?string $moyenContactAnnulation): static
    {
        $this->moyenContactAnnulation = $moyenContactAnnulation;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, StatutCommandeHistorique>
     */
    public function getHistoriqueStatuts(): Collection
    {
        return $this->historiqueStatuts;
    }

    public function addHistoriqueStatut(StatutCommandeHistorique $historiqueStatut): static
    {
        if (!$this->historiqueStatuts->contains($historiqueStatut)) {
            $this->historiqueStatuts->add($historiqueStatut);
            $historiqueStatut->setCommande($this);
        }

        return $this;
    }

    public function removeHistoriqueStatut(StatutCommandeHistorique $historiqueStatut): static
    {
        if ($this->historiqueStatuts->removeElement($historiqueStatut)) {
            if ($historiqueStatut->getCommande() === $this) {
                $historiqueStatut->setCommande(null);
            }
        }

        return $this;
    }

    public function calculatePricing(): void
    {
        if (!$this->menu || !$this->numPersons) {
            return;
        }

        $subtotal = (float) $this->menu->getBasePrice() * $this->numPersons;

        if ($this->numPersons > ($this->menu->getMinPersons() + 5)) {
            $this->discount = (string) ($subtotal * 0.10);
        } else {
            $this->discount = "0.00";
        }

        $this->prixMenu = (string) $subtotal;
        $distanceKm = (float) ($this->distanceLivraisonKm ?? 0);
        $this->fraisLivraison = (string) round(5.00 + ($distanceKm * 0.59), 2);
        $this->prixTotal = (string) ($subtotal - (float) $this->discount + (float) $this->fraisLivraison);
    }
}
