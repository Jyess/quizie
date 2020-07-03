<?php

namespace App\Entity;

use App\Repository\ResultatRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResultatRepository::class)
 */
class Resultat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity=quiz::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $quiz;

    /**
     * @ORM\ManyToMany(targetEntity="Reponse")
     * @ORM\JoinColumn(name="reponseId", referencedColumnName="id")
     */
    private $reponse;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getQuiz(): ?quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getReponse(): ?reponse
    {
        return $this->reponse;
    }

    public function setReponse(?reponse $reponse): self
    {
        $this->reponse = $reponse;

        return $this;
    }
}
