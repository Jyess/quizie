<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le quiz doit avoir un nom.")
     * @Assert\Length(max = 255, maxMessage = "Le nom du quiz doit faire moins de 255 caractères.")
     */
    private $intitule;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Expression("(this.getPlageHoraireDebut() and this.getPlageHoraireFin() and this.getPlageHoraireDebut() < this.getPlageHoraireFin()) or (this.getPlageHoraireDebut() == false and this.getPlageHoraireFin() == false)",
     * message = "La date de début doit être antérieure à la date de fin.")
     */
    private $plageHoraireDebut;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $plageHoraireFin;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $cleAcces;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateurCreateur;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="Quiz")
     * @ORM\JoinColumn(nullable=false)
     */
    private $questions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(?string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
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

    public function getUtilisateurCreateur(): ?Utilisateur
    {
        return $this->utilisateurCreateur;
    }

    public function setUtilisateurCreateur(?Utilisateur $utilisateurCreateur): self
    {
        $this->utilisateurCreateur = $utilisateurCreateur;

        return $this;
    }

    public function getQuestions(): ?Question
    {
        return $this->questions;
    }

    /**
     * Génère une chaîne de caractères alphanumérique aléatoire de 5 caractères par défaut.
     * @param int $length
     * @return string
     */
    public function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            //prend un chiffre random entre le premier caractere et le dernier
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
