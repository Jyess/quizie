<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur2;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(\Doctrine\Persistence\ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $utilisateur = new Utilisateur2();

            $utilisateur->setNom($faker->lastName());
            $utilisateur->setPrenom($faker->firstName());
            $utilisateur->setEmail($faker->email());
            $utilisateur->setMotDePasse($faker->password());

            $manager->persist($utilisateur);
        }

        $manager->flush();
    }
}
