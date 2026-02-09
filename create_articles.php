<?php
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use App\Entity\Article;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

$entityManager = \Doctrine\ORM\EntityManager::create(
    \Doctrine\DBAL\DriverManager::getConnection(['url' => $_ENV['DATABASE_URL']]),
    new \Doctrine\ORM\Configuration()
);

$doctorRepo = $entityManager->getRepository('App\Entity\Doctor');
$specialiteRepo = $entityManager->getRepository('App\Entity\Specialite');
$tagRepo = $entityManager->getRepository('App\Entity\Tag');

$doctors = $doctorRepo->findAll();
$specialites = $specialiteRepo->findAll();
$tags = $tagRepo->findAll();

if (count($doctors) > 0 && count($specialites) > 0) {
    $articlesData = [
        [
            'titre' => 'Les bienfaits de l\'exercice physique',
            'contenu' => 'L\'exercice physique régulier est essentiel pour maintenir une bonne santé. Il améliore la circulation sanguine, renforce les muscles et os, et aide à prévenir de nombreuses maladies chroniques. Il est recommandé de faire au moins 150 minutes d\'exercice modéré par semaine pour les adultes. L\'exercice consiste en un ensemble d\'activités qui sollicitent les muscles de manière à en augmenter ou maintenir la force ou l\'endurance.'
        ],
        [
            'titre' => 'Prévention du diabète',
            'contenu' => 'Le diabète est une maladie chronique qui affecte la capacité du corps à réguler la glycémie. La prévention commence par des changements de mode de vie, y compris une alimentation équilibrée, l\'exercice régulier et la gestion du poids. Une detección précoce et un traitement approprié peuvent minimiser les complications. L\'éducation du patient est cruciale pour une gestion efficace de la maladie.'
        ],
        [
            'titre' => 'Gestion de l\'hypertension artérielle',
            'contenu' => 'L\'hypertension artérielle est une condition grave qui augmente le risque de maladie cardiaque et d\'accident vasculaire cérébral. La gestion comprend la modification du régime alimentaire, la réduction du sodium, l\'exercice régulier et la prise de médicaments prescrits. Le suivi régulier de la tension artérielle et la conformité aux traitements sont essentiels pour contrôler cette condition.'
        ],
        [
            'titre' => 'Soins dermatologiques au quotidien',
            'contenu' => 'La peau est l\'organe le plus grand du corps et mérite une attention particulière. Les soins dermatologiques appropriés incluent le nettoyage régulier, l\'hydratation, la protection contre le soleil et l\'utilisation de produits adaptés au type de peau. La consultation régulière avec un dermatologue peut aider à prévenir et traiter les problèmes cutanés avant qu\'ils ne deviennent graves.'
        ],
    ];
    
    foreach ($articlesData as $data) {
        $article = new Article();
        $article->setTitre($data['titre']);
        $article->setContenu($data['contenu']);
        $article->setAuteur($doctors[array_rand($doctors)]);
        $article->setSpecialite($specialites[array_rand($specialites)]);
        $article->setStatut('publie');
        
        if (count($tags) > 0) {
            $article->addTag($tags[array_rand($tags)]);
        }
        
        $entityManager->persist($article);
    }
    
    $entityManager->flush();
    echo "Articles créés avec succès!\n";
} else {
    echo "Données insuffisantes (doctors: " . count($doctors) . ", specialites: " . count($specialites) . ")\n";
}
?>
