<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ForumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Les données du forum sont déjà créées dans AppFixtures
        // Ce fichier est gardé pour compatibilité mais ne crée rien
    }
}