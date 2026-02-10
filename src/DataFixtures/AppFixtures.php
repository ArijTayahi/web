<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Role;
use App\Entity\Specialite;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create roles
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $manager->persist($roleUser);

        $rolePatient = new Role();
        $rolePatient->setName('ROLE_PATIENT');
        $manager->persist($rolePatient);

        $rolePhysician = new Role();
        $rolePhysician->setName('ROLE_PHYSICIAN');
        $manager->persist($rolePhysician);

        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        $roleSuperAdmin = new Role();
        $roleSuperAdmin->setName('ROLE_SUPER_ADMIN');
        $manager->persist($roleSuperAdmin);

        $manager->flush();

        // Super Admin User
        $superAdmin = new User();
        $superAdmin->setUsername('superadmin');
        $superAdmin->setEmail('superadmin@medilab.local');
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'SuperAdmin@2026'));
        $superAdmin->setIsActive(true);
        $superAdmin->addRoleEntity($roleSuperAdmin);
        $superAdmin->addRoleEntity($roleAdmin);
        $manager->persist($superAdmin);

        // Admin User
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@medilab.local');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin@2026'));
        $admin->setIsActive(true);
        $admin->addRoleEntity($roleAdmin);
        $manager->persist($admin);

        // Doctor users with profiles
        $doctors = [
            ['username' => 'dr.smith', 'email' => 'dr.smith@medilab.local', 'password' => 'DoctorSmith@2026', 'license' => 'LIC-001-SMITH'],
            ['username' => 'dr.johnson', 'email' => 'dr.johnson@medilab.local', 'password' => 'DoctorJohnson@2026', 'license' => 'LIC-002-JOHNSON'],
            ['username' => 'dr.williams', 'email' => 'dr.williams@medilab.local', 'password' => 'DoctorWilliams@2026', 'license' => 'LIC-003-WILLIAMS'],
            ['username' => 'dr.brown', 'email' => 'dr.brown@medilab.local', 'password' => 'DoctorBrown@2026', 'license' => 'LIC-004-BROWN'],
        ];

        foreach ($doctors as $doctorData) {
            $user = new User();
            $user->setUsername($doctorData['username']);
            $user->setEmail($doctorData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $doctorData['password']));
            $user->setIsActive(true);
            $user->addRoleEntity($rolePhysician);
            $manager->persist($user);

            $doctor = new Doctor();
            $doctor->setUser($user);
            $doctor->setLicenseCode($doctorData['license']);
            $doctor->setIsCertified(true);
            $manager->persist($doctor);
        }

        // Patient users with profiles
        $patients = [
            ['username' => 'patient1', 'email' => 'patient1@medilab.local', 'password' => 'Patient@2026', 'region' => 'North Region'],
            ['username' => 'patient2', 'email' => 'patient2@medilab.local', 'password' => 'Patient@2026', 'region' => 'South Region'],
            ['username' => 'patient3', 'email' => 'patient3@medilab.local', 'password' => 'Patient@2026', 'region' => 'East Region'],
            ['username' => 'patient4', 'email' => 'patient4@medilab.local', 'password' => 'Patient@2026', 'region' => 'West Region'],
            ['username' => 'patient5', 'email' => 'patient5@medilab.local', 'password' => 'Patient@2026', 'region' => 'Central Region'],
            ['username' => 'patient6', 'email' => 'patient6@medilab.local', 'password' => 'Patient@2026', 'region' => 'North Region'],
            ['username' => 'patient7', 'email' => 'patient7@medilab.local', 'password' => 'Patient@2026', 'region' => 'South Region'],
            ['username' => 'patient8', 'email' => 'patient8@medilab.local', 'password' => 'Patient@2026', 'region' => 'East Region'],
        ];

        foreach ($patients as $patientData) {
            $user = new User();
            $user->setUsername($patientData['username']);
            $user->setEmail($patientData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $patientData['password']));
            $user->setIsActive(true);
            $user->addRoleEntity($rolePatient);
            $manager->persist($user);

            $patient = new Patient();
            $patient->setUser($user);
            $patient->setRegion($patientData['region']);
            $manager->persist($patient);
        }

        $manager->flush();

        // Create Specialites
        $specialites = [];
        $specialiteNames = [
            'Cardiologie',
            'Neurologie',
            'Dermatologie',
            'Pédiatrie',
            'Gastroentérologie',
            'Pneumologie',
            'Rhumatologie',
            'Endocrinologie'
        ];
        
        foreach ($specialiteNames as $name) {
            $specialite = new Specialite();
            $specialite->setNom($name);
            $specialite->setDescription("Spécialité médicale en $name avec expertise reconnue");
            $manager->persist($specialite);
            $specialites[] = $specialite;
        }

        $manager->flush();

        // Create Tags
        $tags = [];
        $tagNames = [
            'Santé',
            'Prévention',
            'Hygiène',
            'Nutrition',
            'Exercice',
            'Bien-être',
            'Diabète',
            'Hypertension',
            'Allergie',
            'Immunité',
            'Sommeil',
            'Stress'
        ];
        
        foreach ($tagNames as $name) {
            $tag = new Tag();
            $tag->setNom($name);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        $manager->flush();

        // Get doctors to associate with articles
        $doctors = $manager->getRepository(Doctor::class)->findAll();

        // Create Articles
        $articles = [
            [
                'titre' => 'Les bienfaits de l\'exercice régulier pour votre cœur',
                'contenu' => 'L\'exercice régulier est essentiel pour maintenir une bonne santé cardiaque. Des études scientifiques montrent que 30 minutes d\'activité physique par jour réduisent le risque de maladies cardiovasculaires de 35%. Cet article explore les différents types d\'exercices recommandés par les cardiologues modernes et comment les intégrer dans votre routine quotidienne. Découvrez également les signes d\'alerte à surveiller et quand consulter un professionnel de santé. Notre approche holistique combine l\'exercice, l\'alimentation et la gestion du stress pour une santé optimale.',
                'specialite' => 0,
            ],
            [
                'titre' => 'Comprendre les migraines: causes et solutions',
                'contenu' => 'Les migraines affectent plus de 12% de la population mondiale. Dans cet article détaillé, nous explorons les causes neurobiologiques des migraines, les facteurs déclencheurs courants et les stratégies de prévention efficaces. Découvrez les derniers traitements pharmacologiques et non pharmacologiques approuvés par les neurologues. Nous discutons également de l\'importance du suivi et de la tenue d\'un journal des migraines pour identifier vos déclencheurs personnels et gérer efficacement cette condition chronique.',
                'specialite' => 1,
            ],
            [
                'titre' => 'Soins de la peau: guide complet pour tous les types de peau',
                'contenu' => 'Une routine de soins de la peau adaptée à votre type de peau est fondamentale. Les dermatologues recommandent une approche personnalisée et cohérente. Cet article couvre les principes de nettoyage, d\'hydratation et de protection solaire essentiels. Apprenez à identifier votre type de peau, les ingrédients à privilégier et ceux à éviter. Nous partageons également les routines recommandées pour différentes conditions: acné, sécheresse, sensibilité et vieillissement. Consultez un dermatologue pour les problèmes persistants.',
                'specialite' => 2,
            ],
            [
                'titre' => 'Développement de l\'enfant: étapes essentielles',
                'contenu' => 'Le développement pédiatrique suit des étapes clés que tout parent devrait connaître. De la naissance à 5 ans, les enfants acquièrent des compétences motrices, cognitives et sociales essentielles. Cet article décrit les jalons du développement normal et les signes de retard potentiel. Les pédiatres soulignent l\'importance de la stimulation précoce et de la détection rapide des problèmes. Nous fournissons des conseils pratiques pour soutenir le développement sain de votre enfant et savoir quand chercher une aide professionnelle.',
                'specialite' => 3,
            ],
            [
                'titre' => 'Santé digestive: l\'importance d\'une bonne nutrition',
                'contenu' => 'La santé digestive est la fondation d\'une bonne santé générale. Les gastro-entérologues reconnaissent que la nutrition joue un rôle crucial dans la prévention des maladies digestives. Cet article explore les aliments bénéfiques, ceux à limiter, et l\'importance de la fibre alimentaire. Découvrez comment les fibres, les probiotiques et l\'hydratation soutiennent votre système digestif. Nous discutons également des problèmes courants comme l\'IBS, le reflux acide et les intolérances alimentaires, avec des stratégies de gestion pratiques et basées sur des preuves.',
                'specialite' => 4,
            ],
        ];

        foreach ($articles as $index => $articleData) {
            $article = new Article();
            $article->setTitre($articleData['titre']);
            $article->setContenu($articleData['contenu']);
            $article->setSpecialite($specialites[$articleData['specialite']]);
            $article->setStatut('publie');
            $article->setNbVues(rand(50, 500));
            
            // Associate with doctors
            if (!empty($doctors)) {
                $article->setAuteur($doctors[$index % count($doctors)]);
            }

            // Add tags
            for ($i = 0; $i < 3; $i++) {
                $article->addTag($tags[($index + $i) % count($tags)]);
            }

            $manager->persist($article);
        }

        $manager->flush();

        // Create Product Categories
        $categories = [];
        $categoryNames = ['Antibiotiques', 'Anti-inflammatoires', 'Vitamines', 'Antihistaminiques', 'Antiacides'];
        
        foreach ($categoryNames as $name) {
            $category = new ProductCategory();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        $manager->flush();

        // Create Products
        $products = [
            ['name' => 'Amoxicilline 500mg', 'description' => 'Antibiotique efficace', 'price' => 12.50, 'stock' => 100, 'category' => 0, 'prescription' => true],
            ['name' => 'Ibuprofène 400mg', 'description' => 'Anti-inflammatoire puissant', 'price' => 8.75, 'stock' => 150, 'category' => 1, 'prescription' => false],
            ['name' => 'Vitamine C 1000mg', 'description' => 'Renforce l\'immunité', 'price' => 15.00, 'stock' => 200, 'category' => 2, 'prescription' => false],
            ['name' => 'Claritine 10mg', 'description' => 'Antihistaminique antiallergique', 'price' => 10.50, 'stock' => 80, 'category' => 3, 'prescription' => false],
            ['name' => 'Gaviscon Extra Fort', 'description' => 'Soulage les brûlures d\'estomac', 'price' => 7.25, 'stock' => 120, 'category' => 4, 'prescription' => false],
            ['name' => 'Azithromycine 250mg', 'description' => 'Antibiotique macrolide', 'price' => 18.00, 'stock' => 60, 'category' => 0, 'prescription' => true],
            ['name' => 'Vitamine D3 1000ui', 'description' => 'Santé osseuse', 'price' => 11.30, 'stock' => 180, 'category' => 2, 'prescription' => false],
            ['name' => 'Paracétamol 500mg', 'description' => 'Antalgique et antipyrétique', 'price' => 6.50, 'stock' => 250, 'category' => 1, 'prescription' => false],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setStock($productData['stock']);
            $product->setCategoryId($categories[$productData['category']]);
            $product->setIsAvailable(true);
            $product->setIsPrescriptionRequired($productData['prescription']);
            $product->setBrand('Medilab');
            
            $manager->persist($product);
        }

        $manager->flush();
    }
}
