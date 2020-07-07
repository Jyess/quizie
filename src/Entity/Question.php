<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank(message="Le quiz doit avoir un nom.")
     * @Assert\Length(max = 255, maxMessage = "Le nom du quiz doit faire moins de 255 caractères.")
     */
    private $intitule;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="Le nombre de points pour une bonne réponse doit être positif.")
     */
    private $nbPointsBonneReponse;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Negative(message="Le nombre de points pour une mauvaise réponse doit être négatif.")
     */
    private $nbPointsMauvaiseReponse;

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

    public function getNbPointsMauvaiseReponse(): ?int
    {
        return $this->nbPointsMauvaiseReponse;
    }

    public function setNbPointsMauvaiseReponse(int $nbPointsMauvaiseReponse): self
    {
        $this->nbPointsMauvaiseReponse = $nbPointsMauvaiseReponse;

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
