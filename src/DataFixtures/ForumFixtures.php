<?php
namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Specialite;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ForumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Spécialités
        $specs = [];
        $nomSpecs = ['Cardiologie', 'Dermatologie', 'Pédiatrie'];
        foreach ($nomSpecs as $nom) {
            $spec = new Specialite();
            $spec->setNom($nom);
            $spec->setDescription("Description de $nom");
            $manager->persist($spec);
            $specs[] = $spec;
        }

        // Tags
        $tags = [];
        $nomTags = ['diabète', 'hypertension', 'prévention'];
        foreach ($nomTags as $nom) {
            $tag = new Tag();
            $tag->setNom($nom);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        $manager->flush();
    }
}