<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('appointmentDateTime')
            ->add('duration')
            ->add('status')
            ->add('consultationType')
            ->add('reason')
            ->add('notes')
            ->add('reminderSent')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('cancellationRisk')
            ->add('doctor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('patient', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
