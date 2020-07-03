<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(\Doctrine\Persistence\ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $utilisateur = new Utilisateur();

            $utilisateur->setNom($faker->lastName);
            $utilisateur->setPrenom($faker->firstName);
            $utilisateur->setEmail($faker->email);
            $utilisateur->setPassword('$2a$10$kFgwsoGUUy5MvCNM2XSpJeKETe1XYStd.f1sxxI4pnGkJZYT.Ir3K'); //azerty

            $manager->persist($utilisateur);
        }

        $manager->flush();
    }
}
