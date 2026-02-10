<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\StatusRDVEnum;
use App\Entity\ConsultationTypeEnum;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('appointmentDateTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Appointment Date & Time',
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
            ])
            ->add('status', EnumType::class, [
                'class' => StatusRDVEnum::class,
                'label' => 'Status',
            ])
            ->add('consultationType', EnumType::class, [
                'class' => ConsultationTypeEnum::class,
                'label' => 'Consultation Type',
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
                'required' => false,
            ])
            ->add('notes', TextType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
            ->add('reminderSent', CheckboxType::class, [
                'label' => 'Reminder Sent',
                'required' => false,
            ])
            ->add('cancellationRisk', NumberType::class, [
                'label' => 'Cancellation Risk',
                'required' => false,
                'scale' => 2,
            ])
            ->add('doctor', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getUsername() ?? $user->getEmail();
                },
                'label' => 'Doctor',
            ])
            ->add('patient', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getUsername() ?? $user->getEmail();
                },
                'label' => 'Patient',
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