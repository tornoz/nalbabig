<?php

namespace App\Entity;

use App\Repository\KartennRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KartennRepository::class)]
class Kartenn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $anv = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $niverenn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdf = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $png = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnv(): ?string
    {
        return $this->anv;
    }

    public function setAnv(string $anv): static
    {
        $this->anv = $anv;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getNiverenn(): ?string
    {
        return $this->niverenn;
    }

    public function setNiverenn(string $niverenn): static
    {
        $this->niverenn = $niverenn;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(string $pdf): static
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getPng(): ?string
    {
        return $this->png;
    }

    public function setPng(string $png): static
    {
        $this->png = $png;

        return $this;
    }
}
