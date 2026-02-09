<?php

namespace App\Command;

use App\Entity\Article;
use App\Repository\DoctorRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-articles',
    description: 'Create sample articles for the forum'
)]
class CreateArticlesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DoctorRepository $doctorRepository,
        private SpecialiteRepository $specialiteRepository,
        private TagRepository $tagRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $doctors = $this->doctorRepository->findAll();
        $specialites = $this->specialiteRepository->findAll();
        $tags = $this->tagRepository->findAll();

        if (empty($doctors) || empty($specialites)) {
            $output->writeln('<error>Doctors or specialites not found!</error>');
            return Command::FAILURE;
        }

        $articlesData = [
            [
                'titre' => 'Les bienfaits de l\'exercice physique',
                'contenu' => 'L\'exercice physique régulier est essentiel pour maintenir une bonne santé. Il améliore la circulation sanguine, renforce les muscles et os, et aide à prévenir de nombreuses maladies chroniques. Il est recommandé de faire au moins 150 minutes d\'exercice modéré par semaine pour les adultes. L\'exercice consiste en un ensemble d\'activités qui sollicitent les muscles de manière à en augmenter ou maintenir la force ou l\'endurance.'
            ],
            [
                'titre' => 'Prévention du diabète',
                'contenu' => 'Le diabète est une maladie chronique qui affecte la capacité du corps à réguler la glycémie. La prévention commence par des changements de mode de vie, y compris une alimentation équilibrée, l\'exercice régulier et la gestion du poids. Une détection précoce et un traitement approprié peuvent minimiser les complications. L\'éducation du patient est cruciale pour une gestion efficace de la maladie.'
            ],
            [
                'titre' => 'Gestion de l\'hypertension artérielle',
                'contenu' => 'L\'hypertension artérielle est une condition grave qui augmente le risque de maladie cardiaque et d\'accident vasculaire cérébral. La gestion comprend la modification du régime alimentaire, la réduction du sodium, l\'exercice régulier et la prise de médicaments prescrits. Le suivi régulier de la tension artérielle et la conformité aux traitements sont essentiels pour contrôler cette condition.'
            ],
            [
                'titre' => 'Soins dermatologiques au quotidien',
                'contenu' => 'La peau est l\'organe le plus grand du corps et mérite une attention particulière. Les soins dermatologiques appropriés incluent le nettoyage régulier, l\'hydratation, la protection contre le soleil et l\'utilisation de produits adaptés au type de peau. La consultation régulière avec un dermatologue peut aider à prévenir et traiter les problèmes cutanés avant qu\'ils ne deviennent graves.'
            ],
            [
                'titre' => 'Santé cardiaque et nutrition',
                'contenu' => 'Un cœur sain dépend largement de nos choix alimentaires. Les aliments riches en fibres, les acides gras oméga-3 et les antioxydants peuvent contribuer à réduire le risque de maladie cardiaque. Il est conseillé de limiter la consommation de graisses saturées et de sodium. Une alimentation équilibrée combinée à l\'exercice régulier est la clé pour maintenir une bonne santé cardiaque.'
            ],
        ];

        foreach ($articlesData as $data) {
            $article = new Article();
            $article->setTitre($data['titre']);
            $article->setContenu($data['contenu']);
            $article->setAuteur($doctors[array_rand($doctors)]);
            $article->setSpecialite($specialites[array_rand($specialites)]);
            $article->setStatut('publie');

            if (!empty($tags)) {
                $article->addTag($tags[array_rand($tags)]);
            }

            $this->entityManager->persist($article);
            $output->writeln('<info>Article créé: ' . $data['titre'] . '</info>');
        }

        $this->entityManager->flush();
        $output->writeln('<fg=green>✓ Tous les articles ont été créés avec succès!</>');

        return Command::SUCCESS;
    }
}
