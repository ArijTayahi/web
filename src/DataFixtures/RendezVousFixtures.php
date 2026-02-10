<?php

namespace App\DataFixtures;

use App\Entity\RendezVous;
use App\Entity\User;
use App\Entity\StatusRDVEnum;
use App\Entity\ConsultationTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RendezVousFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer les utilisateurs existants
        $users = $manager->getRepository(User::class)->findAll();
        
        if (count($users) < 2) {
            // Créer des utilisateurs de test
            $doctor = new User();
            $doctor->setEmail('doctor@example.com');
            $doctor->setUsername('Dr. Smith');
            $doctor->setPassword('hashed_password');
            $doctor->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($doctor);
            
            $patient = new User();
            $patient->setEmail('patient@example.com');
            $patient->setUsername('John Doe');
            $patient->setPassword('hashed_password');
            $patient->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($patient);
            
            $manager->flush();
            
            $users = [$doctor, $patient];
        }

        // Créer 5 rendez-vous de test
        for ($i = 1; $i <= 5; $i++) {
            $rendezVous = new RendezVous();
            $rendezVous->setAppointmentDateTime(new \DateTime('+' . $i . ' days 14:00'));
            $rendezVous->setDuration(30 + ($i * 10));
            $rendezVous->setStatus(StatusRDVEnum::CONFIRMED);
            $rendezVous->setConsultationType(ConsultationTypeEnum::IN_PERSON);
            $rendezVous->setReason('Patient consultation ' . $i);
            $rendezVous->setNotes('Important appointment');
            $rendezVous->setReminderSent(false);
            $rendezVous->setCreatedAt(new \DateTime());
            $rendezVous->setDoctor($users[0]);
            $rendezVous->setPatient($users[1]);
            
            $manager->persist($rendezVous);
        }

        $manager->flush();
    }
}