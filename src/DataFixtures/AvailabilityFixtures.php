<?php

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\User;
use App\Entity\DayOfWeekEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AvailabilityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer un utilisateur existant (docteur)
        $doctor = $manager->getRepository(User::class)->findOneBy(['username' => 'Dr. Smith']);

        // Si aucun docteur trouvé, essayer de récupérer le premier utilisateur
        if (!$doctor) {
            $doctor = $manager->getRepository(User::class)->findOneBy([], ['id' => 'ASC']);
        }

        // Si toujours aucun utilisateur, créer un docteur de test minimal
        if (!$doctor) {
            $doctor = new User();
            $doctor->setEmail('doctor@example.com');
            $doctor->setUsername('Dr. Smith');
            $doctor->setPassword('hashed_password'); // ok pour fixtures (pas besoin d'encoder ici)
            $doctor->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($doctor);
            $manager->flush();
        }

        // Créer des disponibilités de test
        $days = [
            DayOfWeekEnum::MONDAY,
            DayOfWeekEnum::TUESDAY,
            DayOfWeekEnum::WEDNESDAY,
            DayOfWeekEnum::THURSDAY,
            DayOfWeekEnum::FRIDAY,
        ];

        foreach ($days as $day) {
            $availability = new Availability();
            $availability->setDayOfWeek($day);
            $availability->setStartTime(new \DateTime('08:00'));
            $availability->setEndTime(new \DateTime('17:00'));
            $availability->setRecurring(true);
            $availability->setDoctor($doctor);

            $manager->persist($availability);
        }

        $manager->flush();
    }
}