<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type_event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEvent(): ?string
    {
        return $this->type_event;
    }

    public function setTypeEvent(string $type_event): static
    {
        $this->type_event = $type_event;

        return $this;
    }
}
