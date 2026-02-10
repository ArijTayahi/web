<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\Doctor;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('doctor', EntityType::class, [
                'class' => Doctor::class,
                'choice_label' => function (Doctor $doctor) {
                    return $doctor->getUser()->getUsername();
                },
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('d')
                        ->leftJoin('d.user', 'u')
                        ->where('d.isCertified = 1')
                        ->orderBy('u.username', 'ASC');
                },
            ])
            ->add('consultationDate', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de consultation est obligatoire.',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Online' => 'online',
                    'Cabinet' => 'cabinet',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
