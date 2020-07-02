<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
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
     * @ORM\Column(type="integer")
     */
    private $nbPointBonneReponse;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbPointMauvaiseReponse;

    /**
     * @ORM\ManyToOne(targetEntity=Quiz::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $idQuiz;

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

    public function getNbPointBonneReponse(): ?int
    {
        return $this->nbPointBonneReponse;
    }

    public function setNbPointBonneReponse(int $nbPointBonneReponse): self
    {
        $this->nbPointBonneReponse = $nbPointBonneReponse;

        return $this;
    }

    public function getNbPointMauvaiseReponse(): ?int
    {
        return $this->nbPointMauvaiseReponse;
    }

    public function setNbPointMauvaiseReponse(int $nbPointMauvaiseReponse): self
    {
        $this->nbPointMauvaiseReponse = $nbPointMauvaiseReponse;

        return $this;
    }

    public function getIdQuiz(): ?Quiz
    {
        return $this->idQuiz;
    }

    public function setIdQuiz(?Quiz $idQuiz): self
    {
        $this->idQuiz = $idQuiz;

        return $this;
    }
}
