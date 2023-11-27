<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ticketNumberPurchased = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $purchaseDate = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticketNumber = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketNumberPurchased(): ?int
    {
        return $this->ticketNumberPurchased;
    }

    public function setTicketNumberPurchased(int $ticketNumberPurchased): static
    {
        $this->ticketNumberPurchased = $ticketNumberPurchased;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(\DateTimeImmutable $purchaseDate): static
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getTicketNumber(): ?Ticket
    {
        return $this->ticketNumber;
    }

    public function setTicketNumber(?Ticket $ticketNumber): static
    {
        $this->ticketNumber = $ticketNumber;

        return $this;
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }
}
