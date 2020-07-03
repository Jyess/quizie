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
    private $nbPointsBonneReponse;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbPointMauvaiseReponse;

    /**
     * @ORM\ManyToOne(targetEntity=Quiz::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $quiz;

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

    public function getNbPointsBonneReponse(): ?int
    {
        return $this->nbPointsBonneReponse;
    }

    public function setNbPointsBonneReponse(int $nbPointsBonneReponse): self
    {
        $this->nbPointsBonneReponse = $nbPointsBonneReponse;

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

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }
}
