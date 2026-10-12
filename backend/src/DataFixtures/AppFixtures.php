<?php

namespace App\DataFixtures;

use App\Entity\Candidat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // Interface pour hasher les mots de passe
    private UserPasswordHasherInterface $userPasswordHasher;

    // Injection de dépendance du hasher
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur Administrateur
        $admin = new Candidat();
        $admin->setEmail("admin@bookapi.com");
        $admin->setRole("ROLE_ADMIN");
        // Hashage du mot de passe "password"
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
        $admin->setNom("Admin");
        $admin->setPrenom("System");
        // Persistance de l'entité (préparation à la sauvegarde)
        $manager->persist($admin);

        // Création d'un utilisateur classique
        $user = new Candidat();
        $user->setEmail("user@bookapi.com");
        $user->setRole("ROLE_USER");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setNom("John");
        $user->setPrenom("Doe");
        $manager->persist($user);

        // Création de plusieurs utilisateurs via une boucle
        for ($i = 0; $i < 5; $i++) {
            $userLoop = new Candidat();
            $userLoop->setEmail("user" . $i . "@bookapi.com");
            $userLoop->setRole("ROLE_USER");
            $userLoop->setPassword($this->userPasswordHasher->hashPassword($userLoop, "password"));
            $userLoop->setNom("First" . $i);
            $userLoop->setPrenom("Last" . $i);
            $manager->persist($userLoop);
        }

        // Exécution des requêtes SQL pour sauvegarder en base
        $manager->flush();
    }
}
