<?php

namespace App\Form;

use App\Entity\Availability;
use App\Entity\DayOfWeekEnum;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', EnumType::class, [
                'class' => DayOfWeekEnum::class,
                'label' => 'Day of Week',
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Start Time',
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'End Time',
            ])
            ->add('recurring', CheckboxType::class, [
                'label' => 'Recurring',
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
                'required' => false,
            ])
            ->add('doctor', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getUsername() ?? $user->getEmail();
                },
                'label' => 'Doctor',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Availability::class,
        ]);
    }
}