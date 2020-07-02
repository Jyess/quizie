<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReponseRepository::class)
 */
class Reponse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $intitule;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vraiFaux;

    /**
     * @ORM\OneToOne(targetEntity=Question::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $idQuestion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getVraiFaux(): ?bool
    {
        return $this->vraiFaux;
    }

    public function setVraiFaux(bool $vraiFaux): self
    {
        $this->vraiFaux = $vraiFaux;

        return $this;
    }

    public function getIdQuestion(): ?Question
    {
        return $this->idQuestion;
    }

    public function setIdQuestion(Question $idQuestion): self
    {
        $this->idQuestion = $idQuestion;

        return $this;
    }
}
