<?php

namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatRepository::class)]
class Stat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $hitAt = null;

    #[ORM\ManyToOne(inversedBy: 'stats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Image $image = null;

    #[ORM\ManyToOne(inversedBy: 'stats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeStat $id_type = null;

    public function __construct()
    {
        $this->hitAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHitAt(): ?\DateTimeImmutable
    {
        return $this->hitAt;
    }

    public function setHitAt(?\DateTimeImmutable $hitAt): static
    {
        $this->hitAt = $hitAt;
        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getIdType(): ?TypeStat
    {
        return $this->id_type;
    }

    public function setIdType(?TypeStat $id_type): static
    {
        $this->id_type = $id_type;

        return $this;
    }
}
