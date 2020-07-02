<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuizRepository::class)
 */
class Quiz
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $plageHoraireDebut;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $plageHoraireFin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cleAcces;

    /**
     * @ORM\OneToMany(targetEntity=Utilisateur::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $idUtilisateurCreateur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlageHoraireDebut(): ?\DateTimeInterface
    {
        return $this->plageHoraireDebut;
    }

    public function setPlageHoraireDebut(?\DateTimeInterface $plageHoraireDebut): self
    {
        $this->plageHoraireDebut = $plageHoraireDebut;

        return $this;
    }

    public function getPlageHoraireFin(): ?\DateTimeInterface
    {
        return $this->plageHoraireFin;
    }

    public function setPlageHoraireFin(?\DateTimeInterface $plageHoraireFin): self
    {
        $this->plageHoraireFin = $plageHoraireFin;

        return $this;
    }

    public function getCleAcces(): ?string
    {
        return $this->cleAcces;
    }

    public function setCleAcces(?string $cleAcces): self
    {
        $this->cleAcces = $cleAcces;

        return $this;
    }

    public function getIdUtilisateurCreateur(): ?Utilisateur
    {
        return $this->idUtilisateurCreateur;
    }

    public function setIdUtilisateurCreateur(?Utilisateur $idUtilisateurCreateur): self
    {
        $this->idUtilisateurCreateur = $idUtilisateurCreateur;

        return $this;
    }
}
